<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookConfigController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(Webhook::where('company_id', $this->companyId($request))->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'url'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'max:80'],
            'is_active' => ['boolean'],
        ]);

        return $this->success(Webhook::create([
            'company_id' => $this->companyId($request),
            'url' => $data['url'],
            'events' => $data['events'],
            'secret' => Str::random(32),
            'is_active' => $data['is_active'] ?? true,
        ]), 'Webhook criado.', 201);
    }

    public function update(Request $request, Webhook $webhook)
    {
        $this->authorizeWebhook($request, $webhook);
        $data = $request->validate([
            'url' => ['sometimes', 'url'],
            'events' => ['sometimes', 'array', 'min:1'],
            'is_active' => ['boolean'],
        ]);
        $webhook->update($data);

        return $this->success($webhook);
    }

    public function destroy(Request $request, Webhook $webhook)
    {
        $this->authorizeWebhook($request, $webhook);
        $webhook->delete();

        return $this->success(null, 'Webhook excluido.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeWebhook(Request $request, Webhook $webhook): void
    {
        abort_if($webhook->company_id !== $this->companyId($request), 403);
    }
}
