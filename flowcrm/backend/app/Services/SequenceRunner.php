<?php

namespace App\Services;

use App\Models\FollowUpSequence;
use App\Models\SequenceEnrollment;
use App\Models\Task;
use App\Services\Whatsapp\WhatsappService;
use Carbon\Carbon;

class SequenceRunner
{
    public function __construct(
        private readonly WhatsappService $whatsapp,
    ) {}

    public function enrollLead(FollowUpSequence $sequence, int $leadId): void
    {
        $firstStep = $sequence->steps()->orderBy('position')->first();

        SequenceEnrollment::create([
            'sequence_id' => $sequence->id,
            'lead_id' => $leadId,
            'current_step' => 0,
            'status' => 'active',
            'next_run_at' => $firstStep ? now()->addDays($firstStep->delay_days) : null,
        ]);
    }

    public function processDue(): int
    {
        $processed = 0;

        SequenceEnrollment::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->with(['sequence.steps', 'lead'])
            ->each(function (SequenceEnrollment $enrollment) use (&$processed) {
                $steps = $enrollment->sequence->steps;
                $step = $steps->get($enrollment->current_step);

                if (! $step || ! $enrollment->lead) {
                    $enrollment->update(['status' => 'completed', 'next_run_at' => null]);

                    return;
                }

                $this->runStep($enrollment, $step);
                $nextIndex = $enrollment->current_step + 1;
                $nextStep = $steps->get($nextIndex);

                if ($nextStep) {
                    $enrollment->update([
                        'current_step' => $nextIndex,
                        'next_run_at' => Carbon::now()->addDays($nextStep->delay_days),
                    ]);
                } else {
                    $enrollment->update(['status' => 'completed', 'next_run_at' => null]);
                }

                $processed++;
            });

        return $processed;
    }

    private function runStep(SequenceEnrollment $enrollment, $step): void
    {
        $lead = $enrollment->lead;
        $config = $step->action_config ?? [];

        match ($step->action_type) {
            'create_task' => Task::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'title' => $config['title'] ?? 'Follow-up da sequencia',
                'description' => $config['description'] ?? null,
                'priority' => $config['priority'] ?? 'media',
                'status' => 'pendente',
                'due_at' => now(),
            ]),
            'send_whatsapp' => $this->sendWhatsappToLead($lead, $config),
            default => null,
        };
    }

    private function sendWhatsappToLead($lead, array $config): void
    {
        $phone = $lead->whatsapp ?? $lead->phone;

        if (! $phone) {
            return;
        }

        $body = str_replace('{nome}', (string) $lead->name, $config['body'] ?? 'Ola, tudo bem?');
        $conversation = $this->whatsapp->findOrCreateConversation((int) $lead->company_id, $phone, [
            'lead_id' => $lead->id,
            'contact_name' => $lead->name,
        ]);

        $this->whatsapp->sendMessage($conversation, $body, null);
    }
}
