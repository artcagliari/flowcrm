<?php

namespace App\Services\Whatsapp;

use App\Jobs\SendWhatsappMessage;
use App\Models\Activity;
use App\Models\Client;
use App\Models\CompanyIntegration;
use App\Models\Lead;
use App\Models\User;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\AutomationEngine;
use App\Support\Phone;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function __construct(
        private WhatsappProvider $provider,
        private WhatsappProviderFactory $factory,
    ) {}

    public function provider(): WhatsappProvider
    {
        return $this->provider;
    }

    /**
     * Resolve the provider for a specific company, preferring its saved integration
     * credentials and falling back to the server-wide default provider.
     */
    public function providerFor(int $companyId): WhatsappProvider
    {
        $integration = CompanyIntegration::where('company_id', $companyId)
            ->where('provider', 'whatsapp')
            ->where('is_active', true)
            ->first();

        if (! $integration || empty($integration->credentials['provider'])) {
            Log::warning('WhatsApp: empresa sem integracao ativa, usando provider global', [
                'company_id' => $companyId,
            ]);

            return $this->provider;
        }

        return $this->factory->make($this->factory->fromCredentials($integration->credentials));
    }

    /**
     * Find or create a conversation for a company + phone, auto-linking a client/lead when possible.
     */
    public function findOrCreateConversation(int $companyId, string $rawPhone, array $attrs = []): WhatsappConversation
    {
        $phone = Phone::normalizeBr($rawPhone);
        abort_if($phone === null, 422, 'Numero de WhatsApp invalido.');

        $conversation = WhatsappConversation::where('company_id', $companyId)->where('phone', $phone)->first();

        if (! $conversation) {
            [$clientId, $leadId, $name] = $this->matchContact($companyId, $phone);
            $conversation = WhatsappConversation::create([
                'company_id' => $companyId,
                'client_id' => $attrs['client_id'] ?? $clientId,
                'lead_id' => $attrs['lead_id'] ?? $leadId,
                'contact_name' => $attrs['contact_name'] ?? $name ?? $phone,
                'phone' => $phone,
            ]);
        } elseif (! empty($attrs['client_id']) || ! empty($attrs['lead_id'])) {
            $conversation->fill(array_filter([
                'client_id' => $attrs['client_id'] ?? null,
                'lead_id' => $attrs['lead_id'] ?? null,
            ]))->save();
        }

        return $conversation;
    }

    /**
     * Register an outbound message and dispatch it to the provider asynchronously.
     */
    public function sendMessage(WhatsappConversation $conversation, string $body, ?User $user): WhatsappMessage
    {
        $message = $conversation->messages()->create([
            'direction' => 'out',
            'body' => $body,
            'status' => 'pending',
            'sent_by_user_id' => $user?->id,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $this->logActivity($conversation, 'whatsapp_sent', 'Mensagem de WhatsApp enviada: '.\Illuminate\Support\Str::limit($body, 80), $user?->id);

        SendWhatsappMessage::dispatch($message->id);

        return $message;
    }

    /**
     * Register an inbound message coming from the webhook.
     */
    public function receiveMessage(int $companyId, array $inbound): WhatsappMessage
    {
        $conversation = $this->findOrCreateConversation($companyId, $inbound['phone'], [
            'contact_name' => $inbound['contact_name'] ?? null,
        ]);

        // Turn an unknown WhatsApp contact into a CRM lead so the conversation is never orphaned.
        $this->ensureContactLinked($conversation);

        $message = $conversation->messages()->create([
            'direction' => 'in',
            'body' => $inbound['body'] ?? null,
            'media_url' => $inbound['media_url'] ?? null,
            'status' => 'received',
            'external_id' => $inbound['external_id'] ?? null,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);

        $this->logActivity($conversation, 'whatsapp_received', 'Mensagem de WhatsApp recebida: '.\Illuminate\Support\Str::limit((string) ($inbound['body'] ?? '[midia]'), 80), null);
        $this->triggerInboundAutomation($conversation->fresh());

        if (! empty($inbound['body'])) {
            app(WhatsappSchedulingBot::class)->handleInbound($conversation->fresh(), (string) $inbound['body']);
        }

        return $message;
    }

    /**
     * Ensure a conversation is linked to a client or lead, creating a lead when needed.
     */
    public function ensureContactLinked(WhatsappConversation $conversation): void
    {
        if ($conversation->client_id || $conversation->lead_id) {
            return;
        }

        $lead = Lead::create([
            'company_id' => $conversation->company_id,
            'name' => $conversation->contact_name ?: $conversation->phone,
            'phone' => $conversation->phone,
            'whatsapp' => $conversation->phone,
            'origin' => 'whatsapp',
            'status' => 'novo',
            'temperature' => 'morno',
            'last_interaction_at' => now(),
        ]);

        $conversation->update(['lead_id' => $lead->id]);
        $conversation->setRelation('lead', $lead);
    }

    private function triggerInboundAutomation(WhatsappConversation $conversation): void
    {
        $subject = $conversation->client ?: $conversation->lead;

        if (! $subject) {
            return;
        }

        app(AutomationEngine::class)->trigger(
            $conversation->company_id,
            'whatsapp.message_received',
            $subject,
            ['conversation_id' => $conversation->id, 'phone' => $conversation->phone],
        );
    }

    private function matchContact(int $companyId, string $phone): array
    {
        $tail = substr($phone, -8);
        $filter = fn ($q) => $q->where('whatsapp', 'like', "%{$tail}")->orWhere('phone', 'like', "%{$tail}");

        $client = Client::where('company_id', $companyId)->where($filter)->first();
        $lead = $client ? null : Lead::where('company_id', $companyId)->where($filter)->first();

        return [$client?->id, $lead?->id, $client?->name ?? $lead?->name];
    }

    private function logActivity(WhatsappConversation $conversation, string $action, string $description, ?int $userId): void
    {
        if (! $conversation->client_id && ! $conversation->lead_id) {
            return;
        }

        Activity::create([
            'company_id' => $conversation->company_id,
            'user_id' => $userId,
            'client_id' => $conversation->client_id,
            'lead_id' => $conversation->lead_id,
            'subject_type' => WhatsappConversation::class,
            'subject_id' => $conversation->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
