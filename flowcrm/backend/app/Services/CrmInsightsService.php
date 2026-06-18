<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;

class CrmInsightsService
{
    public function forClient(Client $client): array
    {
        $openDeals = Deal::where('client_id', $client->id)->where('status', 'aberto')->get();
        $pendingTasks = Task::where('client_id', $client->id)->whereNotIn('status', ['concluida'])->orderBy('due_at')->limit(3)->get();
        $nextAppointment = Appointment::where('client_id', $client->id)
            ->where(fn ($q) => $q->where('starts_at', '>=', now())->orWhere('start_at', '>=', now()))
            ->orderBy('starts_at')
            ->first();

        $daysSinceContact = $client->last_contact_at
            ? Carbon::parse($client->last_contact_at)->diffInDays(now())
            : null;

        $summary = $this->buildSummary([
            $client->status ? "Status atual: {$this->statusLabel($client->status)}." : null,
            $openDeals->count() > 0
                ? "{$openDeals->count()} oportunidade(s) aberta(s) totalizando R$ ".$this->money($openDeals->sum('value')).'.'
                : 'Nenhuma oportunidade aberta no momento.',
            $daysSinceContact !== null
                ? ($daysSinceContact > 7 ? "Sem contato ha {$daysSinceContact} dias." : "Ultimo contato ha {$daysSinceContact} dia(s).")
                : 'Ainda sem registro de ultimo contato.',
        ]);

        $nextAction = $this->resolveNextAction($pendingTasks, $nextAppointment, $daysSinceContact, $openDeals->isNotEmpty());

        return [
            'summary' => $summary,
            'next_action' => $nextAction,
            'suggested_message' => $this->suggestedMessage($client->name, $nextAction, 'cliente'),
            'signals' => [
                'open_deals' => $openDeals->count(),
                'pipeline_value' => (float) $openDeals->sum('value'),
                'weighted_forecast' => (float) $openDeals->sum(fn (Deal $d) => $d->weightedValue()),
                'days_since_contact' => $daysSinceContact,
                'pending_tasks' => $pendingTasks->count(),
            ],
            'engine' => 'rules',
        ];
    }

    public function forLead(Lead $lead): array
    {
        $pendingTasks = Task::where('lead_id', $lead->id)->whereNotIn('status', ['concluida'])->orderBy('due_at')->limit(3)->get();
        $nextAppointment = Appointment::where('lead_id', $lead->id)
            ->where(fn ($q) => $q->where('starts_at', '>=', now())->orWhere('start_at', '>=', now()))
            ->orderBy('starts_at')
            ->first();

        $daysSince = $lead->last_interaction_at
            ? Carbon::parse($lead->last_interaction_at)->diffInDays(now())
            : null;

        $temperature = match ($lead->temperature) {
            'quente' => 'Lead quente — priorize contato rapido.',
            'morno' => 'Lead morno — mantenha follow-up regular.',
            'frio' => 'Lead frio — reative com proposta de valor.',
            default => null,
        };

        $summary = $this->buildSummary([
            $lead->interest ? "Interesse: {$lead->interest}." : null,
            $lead->origin ? "Origem: {$lead->origin}." : null,
            $temperature,
            $daysSince !== null
                ? ($daysSince > 5 ? "Sem interacao ha {$daysSince} dias." : "Ultima interacao ha {$daysSince} dia(s).")
                : 'Lead ainda sem interacoes registradas.',
        ]);

        $nextAction = $this->resolveNextAction($pendingTasks, $nextAppointment, $daysSince, false, $lead);

        return [
            'summary' => $summary,
            'next_action' => $nextAction,
            'suggested_message' => $this->suggestedMessage($lead->name, $nextAction, 'lead'),
            'signals' => [
                'score' => $lead->score,
                'temperature' => $lead->temperature,
                'days_since_interaction' => $daysSince,
                'pending_tasks' => $pendingTasks->count(),
                'estimated_value' => (float) ($lead->estimated_value ?? 0),
            ],
            'engine' => 'rules',
        ];
    }

    private function buildSummary(array $parts): string
    {
        return collect($parts)->filter()->take(3)->implode(' ');
    }

    private function resolveNextAction($tasks, $appointment, ?int $daysSince, bool $hasDeals, ?Lead $lead = null): string
    {
        $overdue = $tasks->first(fn ($t) => $t->due_at && Carbon::parse($t->due_at)->isPast());
        if ($overdue) {
            return "Concluir tarefa vencida: {$overdue->title}";
        }

        if ($appointment) {
            $when = Carbon::parse($appointment->starts_at ?? $appointment->start_at)->format('d/m/Y H:i');

            return "Preparar reuniao agendada: {$appointment->title} ({$when})";
        }

        if ($daysSince !== null && $daysSince > 7) {
            return 'Retomar contato — lead/cliente sem interacao recente';
        }

        if ($lead && in_array($lead->temperature, ['quente', 'morno'], true) && ($daysSince === null || $daysSince >= 2)) {
            return 'Ligar ou enviar WhatsApp para qualificar necessidade';
        }

        if ($hasDeals) {
            return 'Revisar oportunidades abertas e atualizar proximo passo';
        }

        $nextTask = $tasks->first();
        if ($nextTask) {
            return "Executar tarefa: {$nextTask->title}";
        }

        return 'Registrar proxima interacao na timeline';
    }

    private function suggestedMessage(string $name, string $nextAction, string $type): string
    {
        $firstName = explode(' ', trim($name))[0] ?: $name;

        if (str_contains(strtolower($nextAction), 'retomar')) {
            return "Ola {$firstName}, tudo bem? Passando para retomar nossa conversa e entender como posso ajudar. Tem um minuto esta semana?";
        }

        if (str_contains(strtolower($nextAction), 'reuniao')) {
            return "Ola {$firstName}! Confirmando nosso compromisso. Se precisar reagendar, e so avisar. Ate la!";
        }

        if ($type === 'lead') {
            return "Ola {$firstName}! Vi seu interesse e gostaria de entender melhor sua necessidade para montar a melhor proposta. Podemos conversar?";
        }

        return "Ola {$firstName}! Como posso ajudar com os proximos passos do nosso atendimento?";
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'encaminhado' => 'Encaminhado',
            'ativo' => 'Ativo',
            'em_atendimento' => 'Em atendimento',
            'agendado' => 'Agendado',
            'inativo' => 'Inativo',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function money(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}
