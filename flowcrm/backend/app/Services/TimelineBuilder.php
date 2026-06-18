<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Task;
use App\Models\WhatsappMessage;
use Illuminate\Support\Collection;

class TimelineBuilder
{
    public function forClient(Client $client, int $limit = 50): Collection
    {
        return $this->merge([
            $this->mapActivities(Activity::where('client_id', $client->id)->latest()->limit($limit)->get()),
            $this->mapNotes(Note::where('client_id', $client->id)->with('user:id,name')->latest()->limit($limit)->get()),
            $this->mapTasks(Task::where('client_id', $client->id)->latest()->limit($limit)->get()),
            $this->mapAppointments(Appointment::where('client_id', $client->id)->latest()->limit($limit)->get()),
            $this->mapPayments(Payment::where('client_id', $client->id)->latest()->limit($limit)->get()),
            $this->mapWhatsapp(WhatsappMessage::whereHas('conversation', fn ($q) => $q->where('client_id', $client->id))->latest()->limit($limit)->get()),
        ]);
    }

    public function forLead(Lead $lead, int $limit = 50): Collection
    {
        return $this->merge([
            $this->mapActivities(Activity::where('lead_id', $lead->id)->latest()->limit($limit)->get()),
            $this->mapNotes(Note::where('lead_id', $lead->id)->with('user:id,name')->latest()->limit($limit)->get()),
            $this->mapTasks(Task::where('lead_id', $lead->id)->latest()->limit($limit)->get()),
            $this->mapAppointments(Appointment::where('lead_id', $lead->id)->latest()->limit($limit)->get()),
            $this->mapWhatsapp(WhatsappMessage::whereHas('conversation', fn ($q) => $q->where('lead_id', $lead->id))->latest()->limit($limit)->get()),
        ]);
    }

    private function merge(array $groups): Collection
    {
        return collect($groups)->flatten(1)->sortByDesc('occurred_at')->values()->take(50);
    }

    private function mapActivities($items): Collection
    {
        return $items->map(fn ($a) => [
            'type' => 'activity',
            'id' => $a->id,
            'title' => $a->action,
            'description' => $a->description,
            'occurred_at' => $a->created_at?->toIso8601String(),
            'meta' => $a->metadata,
        ]);
    }

    private function mapNotes($items): Collection
    {
        return $items->map(fn ($n) => [
            'type' => 'note',
            'id' => $n->id,
            'title' => 'Nota',
            'description' => $n->body ?? $n->content,
            'occurred_at' => $n->created_at?->toIso8601String(),
            'meta' => ['user' => $n->user?->name],
        ]);
    }

    private function mapTasks($items): Collection
    {
        return $items->map(fn ($t) => [
            'type' => 'task',
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->status,
            'occurred_at' => ($t->completed_at ?? $t->updated_at)?->toIso8601String(),
            'meta' => ['priority' => $t->priority],
        ]);
    }

    private function mapAppointments($items): Collection
    {
        return $items->map(fn ($a) => [
            'type' => 'appointment',
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->status,
            'occurred_at' => ($a->starts_at ?? $a->start_at ?? $a->created_at)?->toIso8601String(),
            'meta' => ['type' => $a->type],
        ]);
    }

    private function mapPayments($items): Collection
    {
        return $items->map(fn ($p) => [
            'type' => 'payment',
            'id' => $p->id,
            'title' => 'Pagamento',
            'description' => 'R$ '.number_format((float) $p->amount, 2, ',', '.').' - '.$p->status,
            'occurred_at' => ($p->paid_at ?? $p->created_at)?->toIso8601String(),
            'meta' => [],
        ]);
    }

    private function mapWhatsapp($items): Collection
    {
        return $items->map(fn ($m) => [
            'type' => 'whatsapp',
            'id' => $m->id,
            'title' => $m->direction === 'out' ? 'WhatsApp enviado' : 'WhatsApp recebido',
            'description' => $m->body,
            'occurred_at' => $m->created_at?->toIso8601String(),
            'meta' => ['status' => $m->status],
        ]);
    }
}
