<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Whatsapp\MetaCloudApiProvider;
use App\Services\Whatsapp\MetaWebhookVerifier;
use App\Services\Whatsapp\WhatsappProvider;
use App\Services\Whatsapp\WhatsappProviderFactory;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsappWebhookController extends Controller
{
    public function __construct(
        private WhatsappService $service,
        private MetaWebhookVerifier $metaVerifier,
        private WhatsappProviderFactory $providerFactory,
    ) {}

    /**
     * Webhook verification handshake (Meta Cloud API).
     * Meta sends hub.mode, hub.verify_token and hub.challenge.
     * PHP converte pontos em underscores em $_GET (hub.mode → hub_mode); lemos ambas as formas por seguranca.
     */
    public function verify(Request $request, Company $company): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');
        $expected = (string) config('services.whatsapp.webhook_token');

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, (string) $token)) {
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Inbound messages from WhatsApp providers.
     * Meta: X-Hub-Signature-256 (HMAC com App Secret). Nao usa ?token= no POST.
     * A Meta exige HTTP 200 em ate ~20s; erros internos apos autenticacao devem retornar 200 mesmo assim.
     */
    public function receive(Request $request, Company $company)
    {
        $this->assertWebhookAuthentic($request);

        try {
            $payload = $request->all();
            $isMetaPayload = $this->metaVerifier->looksLikeMetaPayload($payload);
            $provider = $this->resolveInboundProvider($company->id, $payload);
            $inbound = $provider->parseInbound($payload);

            if ($inbound) {
                $this->service->receiveMessage($company->id, $inbound);
            } elseif ($isMetaPayload) {
                Log::warning('WhatsApp webhook Meta nao produziu mensagem inbound', [
                    'company_id' => $company->id,
                    'provider' => $provider::class,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('WhatsApp webhook processing failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function assertWebhookAuthentic(Request $request): void
    {
        $relaxed = $this->isRelaxedWebhookEnv();
        $signature = $request->header('X-Hub-Signature-256');
        $isMetaPayload = $this->metaVerifier->looksLikeMetaPayload($request->all());
        $secretConfigured = $this->metaVerifier->isConfigured();

        if ($signature) {
            if (! $secretConfigured) {
                if (! $relaxed) {
                    Log::critical('WHATSAPP_META_APP_SECRET ausente em ambiente nao-local com POST assinado pela Meta.');
                    abort(500, 'Webhook Meta nao configurado no servidor.');
                }
                abort(401, 'Assinatura Meta recebida mas WHATSAPP_META_APP_SECRET nao configurado.');
            }

            abort_unless($this->metaVerifier->verify($request), 401, 'Assinatura Meta invalida.');

            return;
        }

        if ($isMetaPayload) {
            if (! $secretConfigured && ! $relaxed) {
                Log::critical('WHATSAPP_META_APP_SECRET ausente — POST com payload Meta rejeitado.');
                abort(500, 'Webhook Meta nao configurado no servidor.');
            }

            if ($secretConfigured) {
                abort(401, 'Payload Meta sem assinatura X-Hub-Signature-256.');
            }

            return;
        }

        if (! $relaxed) {
            $token = (string) config('services.whatsapp.webhook_token');
            $provided = $request->header('X-Webhook-Token');

            if ($token === '' || $provided === null || $provided === '') {
                Log::critical('Webhook WhatsApp sem autenticacao em ambiente nao-local.');
                abort(500, 'Webhook nao configurado.');
            }

            abort_unless(hash_equals($token, (string) $provided), 401, 'Token de webhook invalido.');

            return;
        }

        $token = (string) config('services.whatsapp.webhook_token');
        $provided = $request->header('X-Webhook-Token');

        if ($token !== '' && $provided !== null && $provided !== '') {
            abort_unless(hash_equals($token, (string) $provided), 401, 'Token de webhook invalido.');
        }
    }

    private function isRelaxedWebhookEnv(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    /**
     * Escolhe o parser correto para o payload recebido.
     * Meta e Evolution tem formatos distintos; a deteccao usa chaves top-level da Meta (object/entry).
     * Quando o payload e Meta, sempre usa MetaCloudApiProvider — nunca o provider Evolution da empresa.
     */
    private function resolveInboundProvider(int $companyId, array $payload): WhatsappProvider
    {
        if ($this->metaVerifier->looksLikeMetaPayload($payload)) {
            $companyProvider = $this->service->providerFor($companyId);

            if ($companyProvider instanceof MetaCloudApiProvider && $companyProvider->isConfigured()) {
                return $companyProvider;
            }

            if ($companyProvider->isConfigured() && ! $companyProvider instanceof MetaCloudApiProvider) {
                Log::warning('WhatsApp webhook Meta recebido mas integracao da empresa nao e Meta', [
                    'company_id' => $companyId,
                    'provider' => $companyProvider::class,
                ]);
            }

            $metaProvider = $this->providerFactory->make([
                'provider' => 'meta',
                'meta' => config('services.whatsapp.meta', []),
            ]);

            if (! $metaProvider->isConfigured()) {
                Log::warning('WhatsApp webhook Meta recebido sem credenciais Meta configuradas', [
                    'company_id' => $companyId,
                ]);
            }

            return $metaProvider;
        }

        return $this->service->providerFor($companyId);
    }
}
