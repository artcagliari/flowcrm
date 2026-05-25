<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Models\LeadStage;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $companyId = $request->attributes->get('current_company')->id;
        $stages = LeadStage::where('company_id', $companyId)->orderBy('position')->get();
        $leads = Lead::where('company_id', $companyId)->with(['stage', 'owner'])->get()->groupBy('lead_stage_id');

        return $this->success($stages->map(fn ($stage) => [
            'id' => $stage->id,
            'name' => $stage->name,
            'position' => $stage->position,
            'leads' => LeadResource::collection($leads->get($stage->id, collect())),
        ]));
    }

    public function move(Request $request, Lead $lead)
    {
        abort_if((int) $lead->company_id !== (int) $request->attributes->get('current_company')->id, 403);
        $data = $request->validate(['lead_stage_id' => ['required', 'exists:lead_stages,id']]);
        $stage = LeadStage::where('company_id', $lead->company_id)->findOrFail($data['lead_stage_id']);
        $lead->update(['lead_stage_id' => $stage->id, 'status' => str($stage->name)->lower()->toString()]);

        return $this->success(new LeadResource($lead->fresh(['stage', 'owner'])), 'Lead movido com sucesso.');
    }
}
