<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
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

        foreach (['status', 'priority', 'temperature', 'origin', 'owner_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return $this->success($this->resource::collection($query->latest()->paginate((int) $request->query('per_page', 15))));
    }

    protected function createRecord(Request $request, array $data)
    {
        $data['company_id'] = $this->companyId($request);
        $record = $this->model::create($data);

        return $this->success(new $this->resource($record->load($this->with)), 'Registro criado com sucesso.', 201);
    }

    protected function updateRecord(Request $request, Model $record, array $data)
    {
        $this->abortIfDifferentCompany($request, $record);
        $record->update($data);

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

        return $this->success(null, 'Registro excluído com sucesso.');
    }

    protected function abortIfDifferentCompany(Request $request, Model $record): void
    {
        abort_if((int) $record->company_id !== $this->companyId($request), 403, 'Registro não pertence à empresa atual.');
    }
}
