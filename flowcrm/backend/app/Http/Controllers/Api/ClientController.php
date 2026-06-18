<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreClientRequest;
use App\Http\Requests\Api\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\ProfessionalCase;
use App\Services\LgpdService;
use Illuminate\Http\Request;

class ClientController extends CrudController
{
    protected string $model = Client::class;
    protected string $resource = ClientResource::class;
    protected array $with = ['owner', 'tags'];

    public function store(StoreClientRequest $request)
    {
        return $this->createRecord($request, $request->validated());
    }
    public function show(Request $request, Client $client)
    {
        $this->abortIfDifferentCompany($request, $client);
        $this->authorize('view', $client);

        return $this->success([
            'client' => new ClientResource($client->load($this->with)),
            'tasks' => $client->tasks()->latest()->get(),
            'appointments' => $client->appointments()->latest()->get(),
            'payments' => $client->payments()->latest()->get(),
            'notes' => $client->notes()->latest()->get(),
            'documents' => $client->documents()->latest()->get(),
            'activities' => $client->activities()->with('user:id,name')->latest()->get(),
            'cases' => ProfessionalCase::where('client_id', $client->id)->latest()->get(),
        ]);
    }

    public function exportData(Request $request, Client $client, LgpdService $lgpd)
    {
        $this->abortIfDifferentCompany($request, $client);
        $this->authorize('view', $client);

        return response()->json($lgpd->exportClient($client));
    }

    public function anonymize(Request $request, Client $client, LgpdService $lgpd)
    {
        $this->abortIfDifferentCompany($request, $client);
        $this->authorize('delete', $client);
        $this->abortUnlessCanManageModule($request, 'clients');

        $client = $lgpd->anonymizeClient($client);
        $client->delete();

        return $this->success($client, 'Dados anonimizados conforme solicitacao LGPD.');
    }

    public function update(UpdateClientRequest $request, Client $client) { return $this->updateRecord($request, $client, $request->validated()); }
    public function destroy(Request $request, Client $client) { return $this->destroyRecord($request, $client); }
}
