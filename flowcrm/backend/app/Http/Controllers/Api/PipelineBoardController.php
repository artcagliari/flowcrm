<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use Illuminate\Http\Request;

class PipelineBoardController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $companyId = $this->companyId($request);
        $pipelineId = $request->query('pipeline_id');

        $pipeline = $pipelineId
            ? Pipeline::where('company_id', $companyId)->findOrFail($pipelineId)
            : Pipeline::where('company_id', $companyId)->orderByDesc('is_default')->first();

        abort_if(! $pipeline, 404, 'Nenhum pipeline configurado.');

        $stages = LeadStage::where('company_id', $companyId)
            ->where('pipeline_id', $pipeline->id)
            ->orderBy('position')
            ->get(['id', 'pipeline_id', 'name', 'position', 'color', 'is_won', 'is_lost']);

        $leads = Lead::where('company_id', $companyId)
            ->whereNotIn('status', ['encaminhado', 'descartado', 'perdido'])
            ->with('owner:id,name')
            ->orderByDesc('updated_at')
            ->get(['id', 'name', 'lead_stage_id', 'phone', 'whatsapp', 'temperature', 'estimated_value', 'score', 'owner_id', 'status', 'interest']);

        $deals = Deal::where('company_id', $companyId)
            ->where('status', 'aberto')
            ->with(['owner:id,name', 'client:id,name', 'lead:id,name'])
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'value', 'probability', 'lead_stage_id', 'lead_id', 'client_id', 'owner_id']);

        $stageIds = $stages->pluck('id')->all();
        $firstStageId = $stages->first()?->id;

        $columns = $stages->map(function (LeadStage $stage) use ($leads, $deals, $firstStageId) {
            $stageLeads = $leads->filter(fn (Lead $lead) => $lead->lead_stage_id === $stage->id
                || ($lead->lead_stage_id === null && $stage->id === $firstStageId));

            $stageDeals = $deals->where('lead_stage_id', $stage->id);

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'position' => $stage->position,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'leads' => $stageLeads->values()->map(fn (Lead $lead) => [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->whatsapp ?: $lead->phone,
                    'temperature' => $lead->temperature,
                    'estimated_value' => (float) ($lead->estimated_value ?? 0),
                    'score' => $lead->score,
                    'interest' => $lead->interest,
                    'owner' => $lead->owner,
                ]),
                'deals' => $stageDeals->values()->map(fn (Deal $deal) => [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'value' => (float) $deal->value,
                    'probability' => $deal->probability,
                    'weighted_value' => $deal->weightedValue(),
                    'client' => $deal->client,
                    'lead' => $deal->lead,
                    'owner' => $deal->owner,
                ]),
                'totals' => [
                    'leads' => $stageLeads->count(),
                    'deals' => $stageDeals->count(),
                    'value' => (float) $stageDeals->sum('value'),
                    'weighted' => (float) $stageDeals->sum(fn (Deal $d) => $d->weightedValue()),
                ],
            ];
        });

        $openDeals = Deal::where('company_id', $companyId)->where('status', 'aberto')->get();

        return $this->success([
            'pipeline' => $pipeline->only(['id', 'name', 'is_default']),
            'columns' => $columns,
            'summary' => [
                'total_leads' => $leads->count(),
                'total_deals' => $openDeals->count(),
                'pipeline_value' => (float) $openDeals->sum('value'),
                'weighted_forecast' => (float) $openDeals->sum(fn (Deal $d) => $d->weightedValue()),
            ],
        ]);
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
