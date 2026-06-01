<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Requests\Api\StoreDocumentRequest;
use App\Http\Requests\Api\StoreNoteRequest;
use App\Http\Requests\Api\StorePaymentRequest;
use App\Http\Requests\Api\StoreTaskRequest;
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
    use RespondsWithJson;

    public function activities(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->activities()->with('user:id,name')->latest()->get()); }
    public function tasks(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->tasks()->with('owner:id,name')->latest()->get()); }
    public function appointments(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->appointments()->with('owner:id,name')->latest()->get()); }
    public function payments(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->payments()->latest()->get()); }
    public function documents(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->documents()->latest()->get()); }
    public function notes(Request $request, Client $client) { $this->authorizeClient($request, $client); return $this->success($client->notes()->with('user:id,name')->latest()->get()); }

    public function storeTask(StoreTaskRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $task = Task::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id, 'owner_id' => $request->user()->id]);
        return $this->success($task, 'Tarefa criada com sucesso.', 201);
    }

    public function storeAppointment(StoreAppointmentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $appointment = Appointment::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id, 'owner_id' => $request->user()->id]);
        return $this->success($appointment, 'Compromisso criado com sucesso.', 201);
    }

    public function storePayment(StorePaymentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $payment = Payment::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id]);
        return $this->success($payment, 'Pagamento criado com sucesso.', 201);
    }

    public function storeNote(StoreNoteRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $note = Note::create([...$request->validated(), 'company_id' => $client->company_id, 'client_id' => $client->id, 'user_id' => $request->user()->id]);
        return $this->success($note, 'Nota criada com sucesso.', 201);
    }

    public function storeDocument(StoreDocumentRequest $request, Client $client)
    {
        $this->authorizeClient($request, $client);
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

        return $this->success($document, 'Documento enviado com sucesso.', 201);
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_if((int) $client->company_id !== (int) $request->attributes->get('current_company')->id, 403, 'Cliente nao pertence a empresa atual.');
    }
}
