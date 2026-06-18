<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Api\Concerns\AuthorizesCompanyAccess;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Notification;
use App\Services\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    use RespondsWithJson, AuthorizesCompanyAccess;

    protected string $model;
    protected string $resource;
    protected array $with = [];

    protected function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    public function index(Request $request)
    {
        $query = $this->model::query()->where('company_id', $this->companyId($request))->with($this->with);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                foreach (['name', 'title', 'description', 'email', 'phone', 'status'] as $column) {
                    if (in_array($column, $q->getModel()->getFillable(), true)) {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        foreach (['status', 'priority', 'origin', 'type', 'payment_method', 'category', 'temperature', 'lead_stage_id', 'user_id', 'owner_id', 'client_id'] as $filter) {
            if ($request->filled($filter) && in_array($filter, $query->getModel()->getFillable(), true)) {
                $query->where($filter, $request->query($filter));
            }
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, [...$query->getModel()->getFillable(), 'id', 'created_at', 'updated_at'], true)) {
            $sortBy = 'created_at';
        }

        return $this->success($this->resource::collection($query->orderBy($sortBy, $sortDir)->paginate((int) $request->query('per_page', 15))));
    }

    protected function createRecord(Request $request, array $data)
    {
        $this->abortUnlessCanManageModule($request, $this->moduleForRecord(new $this->model));

        $data['company_id'] = $this->companyId($request);
        $fillable = (new $this->model)->getFillable();

        if (in_array('user_id', $fillable, true) && empty($data['user_id'])) {
            $data['user_id'] = $request->user()->id;
        }

        if (in_array('owner_id', $fillable, true) && empty($data['owner_id'])) {
            $data['owner_id'] = $data['user_id'] ?? $request->user()->id;
        }

        $record = $this->model::create($data);
        $this->activity($request, 'criado', 'Registro criado.', $record);
        $this->notification($request, $record, 'created');
        $this->dispatchWebhook($request, $record, 'created');

        return $this->success(new $this->resource($record->load($this->with)), 'Registro criado com sucesso.', 201);
    }

    protected function updateRecord(Request $request, Model $record, array $data)
    {
        $this->abortIfDifferentCompany($request, $record);
        $this->authorizeRecord('update', $record);
        $this->abortUnlessCanManageModule($request, $this->moduleForRecord($record));
        $previous = $record->getOriginal();
        $record->update($data);
        $this->activity($request, 'atualizado', 'Registro atualizado.', $record);
        $this->notification($request, $record, 'updated');
        $this->dispatchWebhook($request, $record, 'updated', ['previous' => $previous]);

        if ($record instanceof Client && array_key_exists('status', $data) && ($previous['status'] ?? null) !== $record->status) {
            $this->dispatchWebhook($request, $record, 'status_changed', [
                'previous_status' => $previous['status'] ?? null,
                'status' => $record->status,
            ]);
        }

        return $this->success(new $this->resource($record->fresh($this->with)));
    }

    protected function showRecord(Request $request, Model $record)
    {
        $this->abortIfDifferentCompany($request, $record);
        $this->authorizeRecord('view', $record);

        return $this->success(new $this->resource($record->load($this->with)));
    }

    protected function destroyRecord(Request $request, Model $record)
    {
        $this->abortIfDifferentCompany($request, $record);
        $this->authorizeRecord('delete', $record);
        $this->abortUnlessCanManageModule($request, $this->moduleForRecord($record));
        $record->delete();
        $this->activity($request, 'excluido', 'Registro excluido.', $record);

        return $this->success(null, 'Registro excluido com sucesso.');
    }

    protected function abortIfDifferentCompany(Request $request, Model $record): void
    {
        abort_if((int) $record->company_id !== $this->companyId($request), 403, 'Registro nao pertence a empresa atual.');
    }

    /**
     * Run the model policy for the given ability when one is registered.
     */
    protected function authorizeRecord(string $ability, Model $record): void
    {
        if (\Illuminate\Support\Facades\Gate::getPolicyFor($record) !== null) {
            $this->authorize($ability, $record);
        }
    }

    protected function activity(Request $request, string $action, string $description, Model $record): void
    {
        $clientName = $record instanceof Client ? $record->name : $record->client?->name ?? null;
        $leadName = $record instanceof Lead ? $record->name : $record->lead?->name ?? null;
        $title = $record->title ?? $record->description ?? $record->name ?? class_basename($record);
        $specificAction = match (class_basename($record).':'.$action) {
            'Client:criado' => 'client_created',
            'Client:atualizado' => 'client_updated',
            'Lead:criado' => 'lead_created',
            'Lead:atualizado' => 'lead_updated',
            'Task:criado' => 'task_created',
            'Task:atualizado' => $record->status === 'concluida' ? 'task_completed' : 'task_updated',
            'Appointment:criado' => 'appointment_created',
            'Appointment:atualizado' => $record->status === 'concluido' ? 'appointment_completed' : 'appointment_updated',
            'Payment:criado' => 'payment_created',
            'Payment:atualizado' => $record->status === 'pago' ? 'payment_paid' : 'payment_updated',
            default => strtolower(class_basename($record)).'_'.$action,
        };
        $specificDescription = $clientName
            ? "{$title} - {$description} Cliente: {$clientName}."
            : ($leadName ? "{$title} - {$description} Lead: {$leadName}." : class_basename($record).' '.$description);

        Activity::create([
            'company_id' => $this->companyId($request),
            'user_id' => $request->user()?->id,
            'client_id' => $record->client_id ?? ($record instanceof Client ? $record->id : null),
            'lead_id' => $record->lead_id ?? ($record instanceof Lead ? $record->id : null),
            'subject_type' => $record::class,
            'subject_id' => $record->getKey(),
            'action' => $specificAction,
            'description' => $specificDescription,
        ]);
    }

    protected function notification(Request $request, Model $record, string $event): void
    {
        $map = [
            'Client:created' => ['Cliente criado', 'Um cliente foi cadastrado.', 'success', '/clients/'.($record->id ?? '')],
            'Task:updated' => $record->status === 'concluida' ? ['Tarefa concluida', 'Uma tarefa foi concluida.', 'success', '/tasks'] : null,
            'Payment:updated' => $record->status === 'pago' ? ['Pagamento recebido', 'Um pagamento foi marcado como pago.', 'success', '/finance'] : null,
        ];
        $payload = $map[class_basename($record).':'.$event] ?? null;
        if (! $payload) return;

        Notification::create([
            'company_id' => $this->companyId($request),
            'user_id' => $request->user()?->id,
            'title' => $payload[0],
            'message' => $payload[1],
            'body' => $payload[1],
            'type' => $payload[2],
            'action_url' => $payload[3],
        ]);
    }

    protected function dispatchWebhook(Request $request, Model $record, string $event, array $context = []): void
    {
        $prefix = match ($record::class) {
            Client::class => 'client',
            Lead::class => 'lead',
            Appointment::class => 'appointment',
            default => null,
        };

        if (! $prefix) {
            return;
        }

        app(WebhookDispatcher::class)->dispatch($this->companyId($request), "{$prefix}.{$event}", [
            'event' => "{$prefix}.{$event}",
            'company_id' => $this->companyId($request),
            'data' => $record->toArray(),
            'context' => $context,
        ]);
    }
}
