<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $company = $request->attributes->get('current_company');
        $query = trim((string) $request->query('query', ''));

        if (mb_strlen($query) < 2) {
            return $this->success([
                'clients' => [],
                'leads' => [],
                'tasks' => [],
                'appointments' => [],
                'payments' => [],
                'documents' => [],
                'notes' => [],
                'users' => [],
            ]);
        }

        $like = "%{$query}%";

        return $this->success([
            'clients' => Client::where('company_id', $company->id)
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('whatsapp', 'like', $like))
                ->limit(6)->get(['id', 'name', 'email', 'phone', 'whatsapp', 'status']),
            'leads' => Lead::where('company_id', $company->id)
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('whatsapp', 'like', $like)->orWhere('interest', 'like', $like))
                ->limit(6)->get(['id', 'name', 'email', 'phone', 'whatsapp', 'status', 'temperature']),
            'tasks' => Task::where('company_id', $company->id)
                ->with('client:id,name')
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
                ->limit(6)->get(['id', 'client_id', 'title', 'status', 'priority', 'due_at']),
            'appointments' => Appointment::where('company_id', $company->id)
                ->with('client:id,name')
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhere('location', 'like', $like))
                ->limit(6)->get(['id', 'client_id', 'title', 'status', 'starts_at']),
            'payments' => Payment::where('company_id', $company->id)
                ->with('client:id,name')
                ->where(fn ($q) => $q->where('description', 'like', $like)->orWhere('notes', 'like', $like))
                ->limit(6)->get(['id', 'client_id', 'description', 'amount', 'status', 'due_date']),
            'documents' => Document::where('company_id', $company->id)
                ->with('client:id,name')
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('original_name', 'like', $like)->orWhere('description', 'like', $like))
                ->limit(6)->get(['id', 'client_id', 'name', 'category', 'created_at']),
            'notes' => Note::where('company_id', $company->id)
                ->with('client:id,name')
                ->where(fn ($q) => $q->where('content', 'like', $like))
                ->limit(6)->get(['id', 'client_id', 'content', 'type', 'created_at']),
            'users' => $company->users()
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->limit(6)->get(['users.id', 'users.name', 'users.email', 'users.role', 'users.status']),
        ]);
    }
}
