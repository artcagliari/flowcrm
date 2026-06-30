<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CompanyIntegration;
use App\Models\Lead;
use App\Models\WhatsappConversation;
use App\Services\Whatsapp\WhatsappProviderFactory;
use App\Services\Whatsapp\WhatsappService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsappController extends Controller
{
    use RespondsWithJson;

    public function __construct(private WhatsappService $service) {}

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        $conversations = WhatsappConversation::where('company_id', $companyId)
            ->with(['client:id,name', 'lead:id,name', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate((int) $request->query('per_page', 30));

        return $this->success([
            'conversations' => $conversations,
            'provider_online' => $this->service->providerFor($companyId)->isConfigured(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = WhatsappConversation::where('company_id', $this->companyId($request))->sum('unread_count');

        return $this->success(['unread' => (int) $count]);
    }

    public function settings(Request $request, WhatsappProviderFactory $factory)
    {
        $companyId = $this->companyId($request);
        $integration = CompanyIntegration::where('company_id', $companyId)->where('provider', 'whatsapp')->first();
        $credentials = $integration->credentials ?? [];

        // Never expose secrets back to the client; report only whether they are set.
        return $this->success([
            'provider' => $credentials['provider'] ?? 'log',
            'is_active' => (bool) ($integration->is_active ?? false),
            'base_url' => $credentials['base_url'] ?? null,
            'instance' => $credentials['instance'] ?? null,
            'phone_number_id' => $credentials['phone_number_id'] ?? null,
            'api_version' => $credentials['api_version'] ?? 'v19.0',
            'has_api_key' => ! empty($credentials['api_key']),
            'has_token' => ! empty($credentials['token']),
            'webhook_url' => url("/api/webhooks/whatsapp/{$companyId}"),
            'verify_token_configured' => ! empty(config('services.whatsapp.webhook_token')),
            'meta_app_secret_configured' => ! empty(config('services.whatsapp.meta.app_secret')),
            'provider_online' => $this->service->providerFor($companyId)->isConfigured(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'provider' => ['required', Rule::in(['log', 'evolution', 'meta'])],
            'is_active' => ['boolean'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'instance' => ['nullable', 'string', 'max:120'],
            'token' => ['nullable', 'string', 'max:1024'],
            'phone_number_id' => ['nullable', 'string', 'max:120'],
            'api_version' => ['nullable', 'string', 'max:16'],
        ]);

        $existing = CompanyIntegration::where('company_id', $companyId)->where('provider', 'whatsapp')->first();
        $credentials = $existing->credentials ?? [];

        $credentials['provider'] = $data['provider'];
        foreach (['base_url', 'instance', 'phone_number_id', 'api_version'] as $field) {
            if (array_key_exists($field, $data)) {
                $credentials[$field] = $data[$field];
            }
        }
        // Only overwrite secrets when a new value is provided, so saving the form does not wipe them.
        foreach (['api_key', 'token'] as $secret) {
            if (! empty($data[$secret])) {
                $credentials[$secret] = $data[$secret];
            }
        }

        $integration = CompanyIntegration::updateOrCreate(
            ['company_id' => $companyId, 'provider' => 'whatsapp'],
            ['credentials' => $credentials, 'is_active' => $data['is_active'] ?? false],
        );

        return $this->success([
            'provider' => $credentials['provider'],
            'is_active' => (bool) $integration->is_active,
            'provider_online' => $this->service->providerFor($companyId)->isConfigured(),
        ], 'Configuracao do WhatsApp salva.');
    }

    public function test(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate(['phone' => ['required', 'string', 'max:32']]);

        $phone = Phone::normalizeBr($data['phone']);
        abort_if($phone === null, 422, 'Numero de WhatsApp invalido.');

        $provider = $this->service->providerFor($companyId);

        try {
            $result = $provider->sendText($phone, 'Mensagem de teste do FlowCRM. Integracao funcionando!');

            return $this->success([
                'status' => $result['status'] ?? 'sent',
                'configured' => $provider->isConfigured(),
            ], $provider->isConfigured() ? 'Mensagem de teste enviada.' : 'Provider em modo log: mensagem registrada, nao entregue.');
        } catch (\Throwable $e) {
            return $this->error('Falha ao enviar teste: '.$e->getMessage(), [], 422);
        }
    }

    public function messages(Request $request, WhatsappConversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);

        return $this->success(
            $conversation->messages()->with('sender:id,name')->orderBy('created_at')->paginate((int) $request->query('per_page', 50))
        );
    }

    public function send(Request $request, WhatsappConversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:4096']]);

        $message = $this->service->sendMessage($conversation, $data['body'], $request->user());

        return $this->success($message->load('sender:id,name'), 'Mensagem enfileirada para envio.', 201);
    }

    public function start(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $attrs = [];
        $phone = $data['phone'] ?? null;

        if (! empty($data['client_id'])) {
            $client = Client::where('company_id', $companyId)->findOrFail($data['client_id']);
            $phone = $phone ?: ($client->whatsapp ?: $client->phone);
            $attrs = ['client_id' => $client->id, 'contact_name' => $client->name];
        } elseif (! empty($data['lead_id'])) {
            $lead = Lead::where('company_id', $companyId)->findOrFail($data['lead_id']);
            $phone = $phone ?: ($lead->whatsapp ?: $lead->phone);
            $attrs = ['lead_id' => $lead->id, 'contact_name' => $lead->name];
        }

        abort_if(empty($phone), 422, 'Nenhum numero de WhatsApp disponivel para iniciar a conversa.');

        $conversation = $this->service->findOrCreateConversation($companyId, $phone, $attrs);

        return $this->success($conversation->load(['client:id,name', 'lead:id,name', 'latestMessage']), 'Conversa pronta.', 201);
    }

    public function read(Request $request, WhatsappConversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);
        $conversation->update(['unread_count' => 0]);

        return $this->success($conversation);
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeConversation(Request $request, WhatsappConversation $conversation): void
    {
        abort_if($conversation->company_id !== $this->companyId($request), 403, 'Conversa nao pertence a empresa atual.');
    }
}
