<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Note;
use App\Models\Payment;
use App\Models\ProfessionalCase;
use App\Models\Task;
use App\Models\WhatsappConversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LgpdService
{
    public function exportClient(Client $client): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'client' => $client->only([
                'id', 'name', 'email', 'phone', 'whatsapp', 'document', 'birth_date',
                'address', 'city', 'state', 'status', 'notes', 'created_at', 'updated_at',
            ]),
            'cases' => ProfessionalCase::where('client_id', $client->id)->get(),
            'tasks' => Task::where('client_id', $client->id)->get(['id', 'title', 'status', 'due_at', 'created_at']),
            'appointments' => Appointment::where('client_id', $client->id)->get(['id', 'title', 'starts_at', 'ends_at', 'status', 'type']),
            'notes' => Note::where('client_id', $client->id)->get(['id', 'content', 'type', 'sensitivity_level', 'created_at']),
            'payments' => Payment::where('client_id', $client->id)->get(['id', 'description', 'amount', 'status', 'due_date']),
            'documents' => Document::where('client_id', $client->id)->get(['id', 'name', 'category', 'created_at']),
            'activities' => Activity::where('client_id', $client->id)->get(['id', 'action', 'description', 'created_at']),
            'whatsapp_conversations' => WhatsappConversation::where('client_id', $client->id)
                ->with(['messages' => fn ($q) => $q->select('id', 'conversation_id', 'direction', 'body', 'sensitivity_level', 'created_at')])
                ->get(),
        ];
    }

    public function anonymizeClient(Client $client): Client
    {
        return DB::transaction(function () use ($client) {
            $token = 'anon-'.Str::uuid();

            $client->update([
                'name' => 'Titular anonimizado',
                'email' => null,
                'phone' => null,
                'whatsapp' => null,
                'document' => null,
                'birth_date' => null,
                'address' => null,
                'city' => null,
                'state' => null,
                'notes' => null,
                'status' => 'anonimizado',
                'anonymized_at' => now(),
            ]);

            Note::where('client_id', $client->id)->update([
                'content' => '[conteudo removido por solicitacao LGPD]',
                'sensitivity_level' => 'normal',
            ]);

            WhatsappConversation::where('client_id', $client->id)->each(function (WhatsappConversation $conv) {
                $conv->messages()->update([
                    'body' => '[mensagem removida por solicitacao LGPD]',
                    'media_url' => null,
                    'sensitivity_level' => 'normal',
                ]);
                $conv->update(['contact_name' => 'Anonimizado', 'bot_state' => null]);
            });

            Activity::where('client_id', $client->id)->update([
                'description' => '[registro anonimizado]',
                'metadata' => null,
            ]);

            return $client->fresh();
        });
    }
}
