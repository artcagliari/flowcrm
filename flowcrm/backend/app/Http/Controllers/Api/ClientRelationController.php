<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Api\Concerns\AuthorizesCompanyAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Requests\Api\StoreDocumentRequest;
use App\Http\Requests\Api\StoreNoteRequest;
use App\Http\Requests\Api\StorePaymentRequest;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientRelationController extends Controller
{
    use RespondsWithJson, AuthorizesCompanyAccess;

    public function activities(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->activities()->with('user:id,name')->latest()->get()); }
    public function tasks(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->tasks()->with('owner:id,name')->latest()->get()); }
    public function appointments(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->appointments()->with('owner:id,name')->latest()->get()); }
    public function payments(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->payments()->latest()->get()); }
    public function documents(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->documents()->latest()->get()); }
    public function notes(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->notes()->with('user:id,name')->latest()->get()); }

    public function storeTask(StoreTaskRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $this->abortUnlessCanManageModule($request, 'tasks');
        $task = Task::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id, 'owner_id' => $request->user()->id]);
        $this->activity($request, $client, 'task_created', 'Tarefa criada para o cliente.', $task);
        return $this->success($task, 'Tarefa criada com sucesso.', 201);
    }

    public function storeAppointment(StoreAppointmentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $this->abortUnlessCanManageModule($request, 'appointments');
        $appointment = Appointment::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id, 'owner_id' => $request->user()->id]);
        $this->activity($request, $client, 'appointment_created', 'Compromisso criado para o cliente.', $appointment);
        return $this->success($appointment, 'Compromisso criado com sucesso.', 201);
    }

    public function storePayment(StorePaymentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $this->abortUnlessCanManageModule($request, 'payments');
        $payment = Payment::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id]);
        $this->activity($request, $client, 'payment_created', 'Pagamento criado para o cliente.', $payment);
        return $this->success($payment, 'Pagamento criado com sucesso.', 201);
    }

    public function storeNote(StoreNoteRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $this->abortUnlessCanManageModule($request, 'notes');
        $note = Note::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id]);
        $this->activity($request, $client, 'note_created', 'Nota criada para o cliente.', $note);
        return $this->success($note, 'Nota criada com sucesso.', 201);
    }

    public function storeDocument(StoreDocumentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $this->abortUnlessCanManageModule($request, 'documents');
        $file = $request->file('file');
        $path = $file->store('documents');
        $document = Document::create([
            'company_id' => $client->company_id,
            'client_id' => $client->id,
            'user_id' => $request->user()->id,
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'size_bytes' => $file->getSize(),
            'category' => $request->input('category', 'outros'),
            'description' => $request->input('description'),
        ]);
        $this->activity($request, $client, 'document_created', 'Documento enviado para o cliente.', $document);

        return $this->success($document, 'Documento enviado com sucesso.', 201);
    }

    private function activity(Request $request, Client $client, string $action, string $description, object $subject): void
    {
        Activity::create([
            'company_id' => $client->company_id,
            'user_id' => $request->user()?->id,
            'client_id' => $client->id,
            'lead_id' => $subject->lead_id ?? null,
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'action' => $action,
            'description' => $description.' Cliente: '.$client->name.'.',
        ]);
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_if((int) $client->company_id !== (int) $request->attributes->get('current_company')->id, 403, 'Cliente nao pertence a empresa atual.');
    }
}
