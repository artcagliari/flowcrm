<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreLeadRequest;
use App\Http\Requests\Api\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends CrudController
{
    protected string $model = Lead::class;
    protected string $resource = LeadResource::class;
    protected array $with = ['owner', 'stage', 'tags'];

    public function store(StoreLeadRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Lead $lead) { return $this->showRecord($request, $lead); }
    public function update(UpdateLeadRequest $request, Lead $lead) { return $this->updateRecord($request, $lead, $request->validated()); }
    public function destroy(Request $request, Lead $lead) { return $this->destroyRecord($request, $lead); }

    public function convert(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $client = Client::create([
            'company_id' => $lead->company_id,
            'owner_id' => $lead->owner_id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'whatsapp' => $lead->whatsapp,
            'email' => $lead->email,
            'origin' => $lead->origin,
            'status' => 'ativo',
            'notes' => $lead->notes,
        ]);
        $lead->update(['status' => 'convertido']);

        return $this->success(['lead' => new LeadResource($lead->fresh()), 'client' => new ClientResource($client)], 'Lead convertido em cliente.');
    }

    public function lost(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $data = $request->validate(['lost_reason' => ['nullable', 'string', 'max:255']]);
        $lead->update(['status' => 'perdido', 'lost_reason' => $data['lost_reason'] ?? null]);

        return $this->success(new LeadResource($lead->fresh()), 'Lead marcado como perdido.');
    }
}
