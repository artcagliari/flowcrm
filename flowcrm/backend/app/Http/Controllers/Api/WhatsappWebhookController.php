<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Http\Request;

class WhatsappWebhookController extends Controller
{
    use RespondsWithJson;

    public function __construct(private WhatsappService $service) {}

    public function __invoke(Request $request, Company $company)
    {
        $expected = config('services.whatsapp.webhook_token');
        if ($expected) {
            $provided = $request->query('token') ?? $request->header('X-Webhook-Token');
            abort_unless(hash_equals((string) $expected, (string) $provided), 401, 'Token de webhook invalido.');
        }

        $inbound = $this->service->provider()->parseInbound($request->all());

        if ($inbound) {
            $this->service->receiveMessage($company->id, $inbound);
        }

        // Always 200 so providers do not retry indefinitely on non-message events.
        return $this->success(['received' => (bool) $inbound]);
    }
}
