<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Automation;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(Automation::where('company_id', $this->companyId($request))->latest()->get());
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'trigger_type' => ['required', 'string', 'max:80'],
            'trigger_config' => ['nullable', 'array'],
            'action_type' => ['required', 'string', 'max:80'],
            'action_config' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        return $this->success(Automation::create([...$data, 'company_id' => $companyId]), 'Automacao criada.', 201);
    }

    public function update(Request $request, Automation $automation)
    {
        $this->authorizeAutomation($request, $automation);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'trigger_type' => ['sometimes', 'string', 'max:80'],
            'trigger_config' => ['nullable', 'array'],
            'action_type' => ['sometimes', 'string', 'max:80'],
            'action_config' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);
        $automation->update($data);

        return $this->success($automation->fresh());
    }

    public function destroy(Request $request, Automation $automation)
    {
        $this->authorizeAutomation($request, $automation);
        $automation->delete();

        return $this->success(null, 'Automacao excluida.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeAutomation(Request $request, Automation $automation): void
    {
        abort_if($automation->company_id !== $this->companyId($request), 403);
    }
}
