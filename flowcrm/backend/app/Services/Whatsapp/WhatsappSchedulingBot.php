<?php

namespace App\Services\Whatsapp;

use App\Models\Appointment;
use App\Models\Lead;
use App\Models\ProfessionalCase;
use App\Models\WhatsappConversation;
use Carbon\Carbon;

class WhatsappSchedulingBot
{
    public function __construct(private WhatsappService $whatsapp) {}

    public function handleInbound(WhatsappConversation $conversation, string $body): void
    {
        $text = strtolower(trim($body));
        $state = $conversation->bot_state ?? ['step' => 'menu'];

        if (in_array($text, ['menu', '0', 'voltar'], true)) {
            $this->sendMenu($conversation);

            return;
        }

        match ($state['step'] ?? 'menu') {
            'menu' => $this->handleMenu($conversation, $text),
            'pick_slot' => $this->handlePickSlot($conversation, $text, $state),
            'confirm' => $this->handleConfirm($conversation, $text, $state),
            default => $this->sendMenu($conversation),
        };
    }

    private function handleMenu(WhatsappConversation $conversation, string $text): void
    {
        if (! $conversation->lead_id && ! $conversation->client_id) {
            $lead = Lead::create([
                'company_id' => $conversation->company_id,
                'name' => $conversation->contact_name ?? 'Contato WhatsApp',
                'phone' => $conversation->phone,
                'whatsapp' => $conversation->phone,
                'origin' => 'whatsapp',
                'status' => 'novo',
                'temperature' => 'morno',
            ]);
            $conversation->update(['lead_id' => $lead->id]);
        }

        if (in_array($text, ['1', 'agendar', 'agendamento', 'reuniao', 'consulta'], true)) {
            $this->sendAvailableSlots($conversation);

            return;
        }

        if (in_array($text, ['2', 'humano', 'atendente'], true)) {
            $this->reply($conversation, "Entendido. Um profissional entrara em contato em breve. Digite *menu* para voltar.");

            return;
        }

        $this->sendMenu($conversation);
    }

    private function sendMenu(WhatsappConversation $conversation): void
    {
        $this->reply($conversation, "Ola! Sou o assistente comercial.\n\n*1* — Agendar reuniao\n*2* — Falar com vendedor\n\nDigite o numero da opcao.");
        $conversation->update(['bot_state' => ['step' => 'menu']]);
    }

    private function sendAvailableSlots(WhatsappConversation $conversation): void
    {
        $slots = $this->availableSlots($conversation->company_id);

        if ($slots->isEmpty()) {
            $this->reply($conversation, "Nao ha horarios disponiveis nos proximos dias. Digite *2* para falar com um profissional ou *menu* para voltar.");
            $conversation->update(['bot_state' => ['step' => 'menu']]);

            return;
        }

        $lines = $slots->values()->map(fn ($slot, $i) => ($i + 1).'. '.$slot['label'])->implode("\n");
        $this->reply($conversation, "Horarios disponiveis:\n\n{$lines}\n\nResponda com o *numero* do horario desejado.");
        $conversation->update(['bot_state' => ['step' => 'pick_slot', 'slots' => $slots->values()->all()]]);
    }

    private function handlePickSlot(WhatsappConversation $conversation, string $text, array $state): void
    {
        $index = (int) $text - 1;
        $slots = collect($state['slots'] ?? []);

        if (! $slots->has($index)) {
            $this->reply($conversation, 'Opcao invalida. Digite o numero do horario ou *menu* para voltar.');

            return;
        }

        $slot = $slots[$index];
        $conversation->update(['bot_state' => ['step' => 'confirm', 'slot' => $slot]]);

        $this->reply($conversation, "Confirmar agendamento em *{$slot['label']}*?\n\n*1* — Sim\n*2* — Nao");
    }

    private function handleConfirm(WhatsappConversation $conversation, string $text, array $state): void
    {
        if (! in_array($text, ['1', 'sim', 's'], true)) {
            $this->sendMenu($conversation);

            return;
        }

        $slot = $state['slot'] ?? null;

        if (! $slot) {
            $this->sendMenu($conversation);

            return;
        }

        $startsAt = Carbon::parse($slot['starts_at']);
        $title = $conversation->client_id ? 'Consulta agendada' : 'Primeira consulta — '.($conversation->contact_name ?? 'Contato');

        $appointment = Appointment::create([
            'company_id' => $conversation->company_id,
            'client_id' => $conversation->client_id,
            'lead_id' => $conversation->lead_id,
            'title' => $title,
            'type' => 'consulta',
            'status' => 'agendado',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
        ]);

        app(\App\Services\GoogleCalendarService::class)->pushAppointment($appointment);

        $this->reply($conversation, "Agendamento confirmado para {$slot['label']}. Ate la!");
        $conversation->update(['bot_state' => ['step' => 'menu']]);
    }

    private function availableSlots(int $companyId)
    {
        $slots = collect();
        $cursor = now()->addDay()->startOfDay()->setHour(9);

        for ($day = 0; $day < 5; $day++) {
            foreach ([9, 11, 14, 16] as $hour) {
                $start = $cursor->copy()->addDays($day)->setHour($hour)->setMinute(0);
                if ($start->isPast()) {
                    continue;
                }

                $busy = Appointment::where('company_id', $companyId)
                    ->where('starts_at', $start)
                    ->whereNotIn('status', ['cancelado'])
                    ->exists();

                if (! $busy) {
                    $slots->push([
                        'starts_at' => $start->toIso8601String(),
                        'label' => $start->format('d/m H:i'),
                    ]);
                }
            }
        }

        return $slots->take(8);
    }

    private function reply(WhatsappConversation $conversation, string $body): void
    {
        $this->whatsapp->sendMessage($conversation, $body, null);
    }
}
