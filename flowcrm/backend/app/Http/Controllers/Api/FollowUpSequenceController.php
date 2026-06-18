<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\FollowUpSequence;
use App\Models\FollowUpSequenceStep;
use Illuminate\Http\Request;

class FollowUpSequenceController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(
            FollowUpSequence::where('company_id', $this->companyId($request))->with('steps')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'trigger_type' => ['nullable', 'string', 'max:80'],
            'is_active' => ['boolean'],
            'steps' => ['nullable', 'array'],
            'steps.*.delay_days' => ['required_with:steps', 'integer', 'min:0'],
            'steps.*.action_type' => ['required_with:steps', 'string', 'max:80'],
            'steps.*.action_config' => ['nullable', 'array'],
        ]);

        $sequence = FollowUpSequence::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'trigger_type' => $data['trigger_type'] ?? 'lead_created',
            'is_active' => $data['is_active'] ?? true,
        ]);

        foreach ($data['steps'] ?? [] as $index => $step) {
            FollowUpSequenceStep::create([
                'sequence_id' => $sequence->id,
                'position' => $index,
                'delay_days' => $step['delay_days'],
                'action_type' => $step['action_type'],
                'action_config' => $step['action_config'] ?? null,
            ]);
        }

        return $this->success($sequence->load('steps'), 'Sequencia criada.', 201);
    }

    public function update(Request $request, FollowUpSequence $sequence)
    {
        $this->authorizeSequence($request, $sequence);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'trigger_type' => ['sometimes', 'string', 'max:80'],
            'is_active' => ['boolean'],
        ]);
        $sequence->update($data);

        return $this->success($sequence->fresh('steps'));
    }

    public function destroy(Request $request, FollowUpSequence $sequence)
    {
        $this->authorizeSequence($request, $sequence);
        $sequence->delete();

        return $this->success(null, 'Sequencia excluida.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeSequence(Request $request, FollowUpSequence $sequence): void
    {
        abort_if($sequence->company_id !== $this->companyId($request), 403);
    }
}
