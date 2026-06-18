<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use Illuminate\Http\Request;

class LeadStageManageController extends Controller
{
    use RespondsWithJson;

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'name' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:16'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_won' => ['boolean'],
            'is_lost' => ['boolean'],
        ]);

        $stage = LeadStage::create([
            'company_id' => $companyId,
            'pipeline_id' => $data['pipeline_id'] ?? null,
            'name' => $data['name'],
            'position' => $data['position'] ?? LeadStage::where('company_id', $companyId)->max('position') + 1,
            'color' => $data['color'] ?? '#4F8CFF',
            'is_won' => $data['is_won'] ?? false,
            'is_lost' => $data['is_lost'] ?? false,
        ]);

        return $this->success($stage, 'Etapa criada.', 201);
    }

    public function update(Request $request, LeadStage $stage)
    {
        $this->authorizeStage($request, $stage);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:16'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_won' => ['boolean'],
            'is_lost' => ['boolean'],
        ]);
        $stage->update($data);

        return $this->success($stage->fresh());
    }

    public function destroy(Request $request, LeadStage $stage)
    {
        $this->authorizeStage($request, $stage);
        $stage->delete();

        return $this->success(null, 'Etapa excluida.');
    }

    public function reorder(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate(['stages' => ['required', 'array'], 'stages.*.id' => ['required', 'integer'], 'stages.*.position' => ['required', 'integer']]);

        foreach ($data['stages'] as $item) {
            LeadStage::where('company_id', $companyId)->where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return $this->success(LeadStage::where('company_id', $companyId)->orderBy('position')->get());
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeStage(Request $request, LeadStage $stage): void
    {
        abort_if($stage->company_id !== $this->companyId($request), 403);
    }
}
