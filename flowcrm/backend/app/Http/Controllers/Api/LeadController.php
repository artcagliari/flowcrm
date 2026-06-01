<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreLeadRequest;
use App\Http\Requests\Api\UpdateLeadRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\LeadResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends CrudController
{
    protected string $model = Lead::class;
    protected string $resource = LeadResource::class;
    protected array $with = ['owner', 'stage', 'tags'];

    public function store(StoreLeadRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);

        return $this->success([
            'lead' => new LeadResource($lead->load($this->with)),
            'tasks' => $lead->tasks()->with('owner:id,name')->latest()->get(),
            'appointments' => $lead->appointments()->with('owner:id,name')->latest()->get(),
            'notes' => $lead->notes()->with('user:id,name')->latest()->get(),
            'documents' => $lead->documents()->latest()->get(),
            'activities' => $lead->activities()->with('user:id,name')->latest()->get(),
        ]);
    }
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
        $lead->tasks()->update(['client_id' => $client->id]);
        $lead->appointments()->update(['client_id' => $client->id]);
        $lead->notes()->update(['client_id' => $client->id]);
        $lead->documents()->update(['client_id' => $client->id]);
        $client->tags()->sync($lead->tags()->pluck('tags.id')->all());
        $lead->update(['status' => 'convertido']);
        $this->recordLeadActivity($request, $lead, 'lead_converted', 'Lead convertido em cliente.', ['client_id' => $client->id]);

        return $this->success(['lead' => new LeadResource($lead->fresh($this->with)), 'client' => new ClientResource($client->load(['owner', 'tags']))], 'Lead convertido em cliente.');
    }

    public function lost(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $data = $request->validate(['lost_reason' => ['nullable', 'string', 'max:255']]);
        $lead->update(['status' => 'perdido', 'lost_reason' => $data['lost_reason'] ?? null]);
        $this->recordLeadActivity($request, $lead, 'lead_lost', 'Lead marcado como perdido.', ['lost_reason' => $data['lost_reason'] ?? null]);

        return $this->success(new LeadResource($lead->fresh()), 'Lead marcado como perdido.');
    }

    private function recordLeadActivity(Request $request, Lead $lead, string $action, string $description, array $metadata = []): void
    {
        Activity::create([
            'company_id' => $lead->company_id,
            'user_id' => $request->user()?->id,
            'lead_id' => $lead->id,
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'action' => $action,
            'description' => $description.' Lead: '.$lead->name.'.',
            'metadata' => $metadata,
        ]);
    }
}
