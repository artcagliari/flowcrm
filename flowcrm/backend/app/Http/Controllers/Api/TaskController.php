<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends CrudController
{
    protected string $model = Task::class;
    protected string $resource = TaskResource::class;
    protected array $with = ['owner', 'client', 'lead'];

    public function store(StoreTaskRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Task $task) { return $this->showRecord($request, $task); }
    public function update(UpdateTaskRequest $request, Task $task) { return $this->updateRecord($request, $task, $request->validated()); }
    public function destroy(Request $request, Task $task) { return $this->destroyRecord($request, $task); }
    public function complete(Request $request, Task $task) { return $this->updateRecord($request, $task, ['status' => 'concluída']); }
}
