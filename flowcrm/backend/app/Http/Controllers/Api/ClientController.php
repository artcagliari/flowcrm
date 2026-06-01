<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreClientRequest;
use App\Http\Requests\Api\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends CrudController
{
    protected string $model = Client::class;
    protected string $resource = ClientResource::class;
    protected array $with = ['owner', 'tags'];

    public function store(StoreClientRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Client $client)
    {
        $this->abortIfDifferentCompany($request, $client);

        return $this->success([
            'client' => new ClientResource($client->load($this->with)),
            'tasks' => $client->tasks()->latest()->get(),
            'appointments' => $client->appointments()->latest()->get(),
            'payments' => $client->payments()->latest()->get(),
            'notes' => $client->notes()->latest()->get(),
            'documents' => $client->documents()->latest()->get(),
            'activities' => $client->activities()->with('user:id,name')->latest()->get(),
        ]);
    }
    public function update(UpdateClientRequest $request, Client $client) { return $this->updateRecord($request, $client, $request->validated()); }
    public function destroy(Request $request, Client $client) { return $this->destroyRecord($request, $client); }
}
