<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesCompanyAccess;
use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Services\AutomationEngine;
use App\Services\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DealController extends Controller
{
    use RespondsWithJson, AuthorizesCompanyAccess;

    public function __construct(private AutomationEngine $automations) {}

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $query = Deal::where('company_id', $companyId)->with(['owner:id,name', 'stage', 'client:id,name', 'lead:id,name']);

        foreach (['status', 'owner_id', 'pipeline_id', 'lead_stage_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return $this->success($query->latest()->paginate((int) $request->query('per_page', 15)));
    }

    public function store(Request $request)
    {
        $this->abortUnlessCanManageModule($request, 'leads');
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'lead_stage_id' => ['nullable', 'exists:lead_stages,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $deal = Deal::create([
            ...$data,
            'company_id' => $companyId,
            'owner_id' => $data['owner_id'] ?? $request->user()->id,
            'status' => 'aberto',
        ]);

        $this->automations->trigger($companyId, 'deal.created', $deal);

        return $this->success($deal->load(['owner:id,name', 'stage']), 'Oportunidade criada.', 201);
    }

    public function show(Request $request, Deal $deal)
    {
        $this->authorizeDeal($request, $deal);

        return $this->success($deal->load(['owner:id,name', 'stage', 'client:id,name', 'lead:id,name', 'lostReason']));
    }

    public function update(Request $request, Deal $deal)
    {
        $this->authorizeDeal($request, $deal);
        $this->abortUnlessCanManageModule($request, 'leads');
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:190'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'lead_stage_id' => ['nullable', 'exists:lead_stages,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['aberto', 'ganho', 'perdido'])],
        ]);
        $deal->update($data);

        return $this->success($deal->fresh(['owner:id,name', 'stage']));
    }

    public function destroy(Request $request, Deal $deal)
    {
        $this->authorizeDeal($request, $deal);
        $this->abortUnlessCanManageModule($request, 'leads');
        $deal->delete();

        return $this->success(null, 'Oportunidade excluida.');
    }

    public function won(Request $request, Deal $deal)
    {
        $this->authorizeDeal($request, $deal);
        $deal->update(['status' => 'ganho', 'won_at' => now(), 'probability' => 100]);
        $this->automations->trigger($deal->company_id, 'deal.won', $deal);

        return $this->success($deal->fresh());
    }

    public function lost(Request $request, Deal $deal)
    {
        $this->authorizeDeal($request, $deal);
        $data = $request->validate(['lost_reason_id' => ['required', 'exists:loss_reasons,id']]);
        $deal->update(['status' => 'perdido', 'lost_at' => now(), 'lost_reason_id' => $data['lost_reason_id'], 'probability' => 0]);
        $this->automations->trigger($deal->company_id, 'deal.lost', $deal);

        return $this->success($deal->fresh('lostReason'));
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeDeal(Request $request, Deal $deal): void
    {
        abort_if($deal->company_id !== $this->companyId($request), 403);
    }
}
