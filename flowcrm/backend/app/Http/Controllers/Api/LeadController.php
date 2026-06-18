<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreLeadRequest;
use App\Http\Requests\Api\UpdateLeadRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\LeadResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Lead;
use App\Models\ProfessionalCase;
use App\Services\AuditLogger;
use App\Services\LeadAssigner;
use App\Services\PlanLimiter;
use Illuminate\Http\Request;

class LeadController extends CrudController
{
    protected string $model = Lead::class;
    protected string $resource = LeadResource::class;
    protected array $with = ['owner', 'tags'];

    public function __construct(
        private LeadAssigner $assigner,
        private AuditLogger $audit,
        private PlanLimiter $planLimiter,
    ) {}

    public function store(StoreLeadRequest $request)
    {
        $company = $request->attributes->get('current_company');
        abort_unless($this->planLimiter->canAddLead($company), 403, 'Limite de leads do plano atingido.');

        $data = $request->validated();
        $ownerId = $this->assigner->assignOwner($company, $data['owner_id'] ?? null);
        if ($ownerId) {
            $data['owner_id'] = $ownerId;
        }

        $data['company_id'] = $company->id;
        $data['status'] = $data['status'] ?? 'novo';
        if (empty($data['owner_id'])) {
            $data['owner_id'] = $request->user()->id;
        }

        $this->abortUnlessCanManageModule($request, 'leads');
        $lead = Lead::create($data);
        $this->activity($request, 'criado', 'Contato registrado.', $lead);
        $this->audit->log($request, 'lead.created', $lead, null, $lead->toArray());
        $this->dispatchWebhook($request, $lead, 'created');

        return $this->success(new LeadResource($lead->load($this->with)), 'Contato registrado.', 201);
    }

    public function show(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $this->authorize('view', $lead);

        return $this->success([
            'lead' => new LeadResource($lead->load($this->with)),
            'tasks' => $lead->tasks()->with('owner:id,name')->latest()->get(),
            'appointments' => $lead->appointments()->with('owner:id,name')->latest()->get(),
            'notes' => $lead->notes()->with('user:id,name')->latest()->get(),
            'documents' => $lead->documents()->latest()->get(),
            'activities' => $lead->activities()->with('user:id,name')->latest()->get(),
            'cases' => ProfessionalCase::where('lead_id', $lead->id)->latest()->get(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $old = $lead->toArray();
        $response = $this->updateRecord($request, $lead, $request->validated());
        $this->audit->log($request, 'lead.updated', $lead, $old, $lead->fresh()->toArray());

        return $response;
    }

    public function destroy(Request $request, Lead $lead) { return $this->destroyRecord($request, $lead); }

    public function convert(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $this->authorize('update', $lead);

        $client = Client::create([
            'company_id' => $lead->company_id,
            'owner_id' => $lead->owner_id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'whatsapp' => $lead->whatsapp,
            'email' => $lead->email,
            'origin' => $lead->origin,
            'status' => 'encaminhado',
            'notes' => $lead->notes,
        ]);

        $lead->tasks()->update(['client_id' => $client->id]);
        $lead->appointments()->update(['client_id' => $client->id]);
        $lead->notes()->update(['client_id' => $client->id]);
        $lead->documents()->update(['client_id' => $client->id]);
        $client->tags()->sync($lead->tags()->pluck('tags.id')->all());
        $lead->update(['status' => 'encaminhado']);

        $this->recordLeadActivity($request, $lead, 'lead_forwarded', 'Lead convertido em cliente.', [
            'client_id' => $client->id,
        ]);

        $this->dispatchWebhook($request, $lead, 'forwarded', ['client_id' => $client->id]);
        $this->dispatchWebhook($request, $client, 'created', ['lead_id' => $lead->id]);

        return $this->success([
            'lead' => new LeadResource($lead->fresh($this->with)),
            'client' => new ClientResource($client->load(['owner', 'tags'])),
        ], 'Lead convertido em cliente. Agende o proximo compromisso na Agenda.');
    }

    public function lost(Request $request, Lead $lead)
    {
        $this->abortIfDifferentCompany($request, $lead);
        $this->authorize('update', $lead);
        $data = $request->validate(['lost_reason' => ['nullable', 'string', 'max:255']]);
        $lead->update(['status' => 'descartado', 'lost_reason' => $data['lost_reason'] ?? null]);
        $this->recordLeadActivity($request, $lead, 'lead_discarded', 'Contato descartado.', $data);
        $this->dispatchWebhook($request, $lead->fresh(), 'discarded', $data);

        return $this->success(new LeadResource($lead->fresh($this->with)), 'Contato descartado.');
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
            'description' => $description.' Contato: '.$lead->name.'.',
            'metadata' => $metadata,
        ]);
    }
}
