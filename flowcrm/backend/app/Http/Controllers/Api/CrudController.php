<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    use RespondsWithJson;

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

        foreach (['status', 'priority', 'origin', 'type', 'payment_method', 'category', 'user_id', 'owner_id', 'client_id'] as $filter) {
            if ($request->filled($filter)) {
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

        return $this->success(new $this->resource($record->load($this->with)), 'Registro criado com sucesso.', 201);
    }

    protected function updateRecord(Request $request, Model $record, array $data)
    {
        $this->abortIfDifferentCompany($request, $record);
        $record->update($data);
        $this->activity($request, 'atualizado', 'Registro atualizado.', $record);

        return $this->success(new $this->resource($record->fresh($this->with)));
    }

    protected function showRecord(Request $request, Model $record)
    {
        $this->abortIfDifferentCompany($request, $record);

        return $this->success(new $this->resource($record->load($this->with)));
    }

    protected function destroyRecord(Request $request, Model $record)
    {
        $this->abortIfDifferentCompany($request, $record);
        $record->delete();
        $this->activity($request, 'excluido', 'Registro excluido.', $record);

        return $this->success(null, 'Registro excluido com sucesso.');
    }

    protected function abortIfDifferentCompany(Request $request, Model $record): void
    {
        abort_if((int) $record->company_id !== $this->companyId($request), 403, 'Registro nao pertence a empresa atual.');
    }

    protected function activity(Request $request, string $action, string $description, Model $record): void
    {
        Activity::create([
            'company_id' => $this->companyId($request),
            'user_id' => $request->user()?->id,
            'client_id' => $record->client_id ?? ($record instanceof Client ? $record->id : null),
            'subject_type' => $record::class,
            'subject_id' => $record->getKey(),
            'action' => $action,
            'description' => class_basename($record).' '.$description,
        ]);
    }
}
