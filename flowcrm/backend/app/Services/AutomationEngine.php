<?php

namespace App\Services;

use App\Jobs\SendCrmEmail;
use App\Services\Whatsapp\WhatsappService;
use App\Models\Automation;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

class AutomationEngine
{
    public function __construct(
        private readonly WebhookDispatcher $webhooks,
        private readonly WhatsappService $whatsapp,
    ) {}

    public function trigger(int $companyId, string $triggerType, Model $subject, array $context = []): void
    {
        Automation::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('trigger_type', $triggerType)
            ->each(fn (Automation $automation) => $this->run($automation, $subject, $context));

        $event = str_replace('.', '_', $triggerType);
        $this->webhooks->dispatch($companyId, $event, [
            'trigger' => $triggerType,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'context' => $context,
        ]);
    }

    private function run(Automation $automation, Model $subject, array $context): void
    {
        $config = $automation->action_config ?? [];

        match ($automation->action_type) {
            'create_task' => $this->createTask($automation->company_id, $subject, $config),
            'notify_user' => $this->notifyUser($automation->company_id, $config),
            'send_email' => $this->sendEmail($subject, $config),
            'send_whatsapp' => $this->sendWhatsapp($subject, $config),
            'change_stage' => $this->changeStage($subject, $config),
            default => null,
        };
    }

    private function createTask(int $companyId, Model $subject, array $config): void
    {
        Task::create([
            'company_id' => $companyId,
            'title' => $config['title'] ?? 'Follow-up automatico',
            'description' => $config['description'] ?? null,
            'priority' => $config['priority'] ?? 'media',
            'status' => 'pendente',
            'due_at' => now()->addDays((int) ($config['due_days'] ?? 1)),
            'lead_id' => $subject instanceof Lead ? $subject->id : ($config['lead_id'] ?? null),
            'client_id' => $config['client_id'] ?? null,
        ]);
    }

    private function notifyUser(int $companyId, array $config): void
    {
        if (empty($config['user_id'])) {
            return;
        }

        Notification::create([
            'company_id' => $companyId,
            'user_id' => $config['user_id'],
            'title' => $config['title'] ?? 'Automacao executada',
            'message' => $config['message'] ?? 'Uma automacao foi disparada.',
            'body' => $config['message'] ?? 'Uma automacao foi disparada.',
            'type' => 'info',
            'action_url' => $config['action_url'] ?? null,
        ]);
    }

    private function sendEmail(Model $subject, array $config): void
    {
        $email = $config['to'] ?? ($subject->email ?? null);

        if (! $email) {
            return;
        }

        SendCrmEmail::dispatch($email, $config['subject'] ?? 'Mensagem CRM', $config['body'] ?? '');
    }

    private function sendWhatsapp(Model $subject, array $config): void
    {
        $phone = $config['phone'] ?? ($subject->whatsapp ?? $subject->phone ?? null);

        if (! $phone) {
            return;
        }

        $body = str_replace('{nome}', (string) ($subject->name ?? ''), $config['body'] ?? 'Ola!');
        $conversation = $this->whatsapp->findOrCreateConversation((int) $subject->company_id, $phone, array_filter([
            'lead_id' => $subject instanceof Lead ? $subject->id : ($config['lead_id'] ?? null),
            'client_id' => $config['client_id'] ?? null,
            'contact_name' => $subject->name ?? null,
        ]));

        $this->whatsapp->sendMessage($conversation, $body, null);
    }

    private function changeStage(Model $subject, array $config): void
    {
        if (! $subject instanceof Lead || empty($config['lead_stage_id'])) {
            return;
        }

        $subject->update(['lead_stage_id' => $config['lead_stage_id'], 'stage_entered_at' => now()]);
    }
}
