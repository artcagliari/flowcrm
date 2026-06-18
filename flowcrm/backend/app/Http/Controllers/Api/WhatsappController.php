<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\WhatsappConversation;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Http\Request;

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
            'provider_online' => $this->service->provider()->isConfigured(),
        ]);
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
