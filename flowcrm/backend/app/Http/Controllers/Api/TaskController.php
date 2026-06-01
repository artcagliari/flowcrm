<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\CrmOverdueMarker;
use Illuminate\Http\Request;

class TaskController extends CrudController
{
    protected string $model = Task::class;
    protected string $resource = TaskResource::class;
    protected array $with = ['user', 'owner', 'client'];

    public function index(Request $request)
    {
        app(CrmOverdueMarker::class)->markForCompany($this->companyId($request));

        return parent::index($request);
    }

    public function store(StoreTaskRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Task $task) { return $this->showRecord($request, $task); }
    public function update(UpdateTaskRequest $request, Task $task) { return $this->updateRecord($request, $task, $request->validated()); }
    public function destroy(Request $request, Task $task) { return $this->destroyRecord($request, $task); }
    public function complete(Request $request, Task $task) { return $this->updateRecord($request, $task, ['status' => 'concluida', 'completed_at' => now()]); }
}
