<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use App\Models\Pipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        return $this->success(
            Pipeline::where('company_id', $companyId)
                ->with(['stages' => fn ($q) => $q->orderBy('position')])
                ->orderByDesc('is_default')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_default' => ['boolean'],
            'stages' => ['nullable', 'array'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:120'],
            'stages.*.color' => ['nullable', 'string', 'max:16'],
            'stages.*.is_won' => ['boolean'],
            'stages.*.is_lost' => ['boolean'],
        ]);

        if (! empty($data['is_default'])) {
            Pipeline::where('company_id', $companyId)->update(['is_default' => false]);
        }

        $pipeline = Pipeline::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'is_default' => $data['is_default'] ?? false,
        ]);

        foreach ($data['stages'] ?? [] as $index => $stage) {
            LeadStage::create([
                'company_id' => $companyId,
                'pipeline_id' => $pipeline->id,
                'name' => $stage['name'],
                'position' => $index,
                'color' => $stage['color'] ?? '#4F8CFF',
                'is_won' => $stage['is_won'] ?? false,
                'is_lost' => $stage['is_lost'] ?? false,
            ]);
        }

        return $this->success($pipeline->load('stages'), 'Pipeline criado.', 201);
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'is_default' => ['boolean'],
        ]);

        if (! empty($data['is_default'])) {
            Pipeline::where('company_id', $pipeline->company_id)->where('id', '!=', $pipeline->id)->update(['is_default' => false]);
        }

        $pipeline->update($data);

        return $this->success($pipeline->fresh('stages'));
    }

    public function destroy(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);
        abort_if($pipeline->is_default, 422, 'Nao e possivel excluir o pipeline padrao.');
        $pipeline->delete();

        return $this->success(null, 'Pipeline excluido.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizePipeline(Request $request, Pipeline $pipeline): void
    {
        abort_if($pipeline->company_id !== $this->companyId($request), 403);
    }
}
