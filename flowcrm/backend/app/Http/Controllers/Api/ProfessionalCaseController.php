<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalCase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessionalCaseController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $query = ProfessionalCase::where('company_id', $companyId)
            ->with(['client:id,name', 'owner:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('profession_mode')) {
            $query->where('profession_mode', $request->query('profession_mode'));
        }

        return $this->success($query->latest()->paginate((int) $request->query('per_page', 15)));
    }

    public function store(Request $request)
    {
        $company = $request->attributes->get('current_company');
        $mode = $company->profession_mode ?? 'empresa';

        $rules = [
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:190'],
            'status' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
            'session_frequency' => ['nullable', Rule::in(['semanal', 'quinzenal', 'mensal'])],
        ];

        $data = $request->validate($rules);
        $data['company_id'] = $company->id;
        $data['profession_mode'] = $mode;
        $data['opened_at'] = now();
        $data['owner_id'] = $request->user()->id;

        return $this->success(ProfessionalCase::create($data)->load(['client:id,name']), 'Caso criado.', 201);
    }

    public function show(Request $request, ProfessionalCase $case)
    {
        $this->authorizeCase($request, $case);

        return $this->success($case->load(['client', 'owner:id,name', 'lead:id,name']));
    }

    public function update(Request $request, ProfessionalCase $case)
    {
        $this->authorizeCase($request, $case);
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:190'],
            'status' => ['sometimes', 'string', 'max:40'],
            'session_frequency' => ['nullable', Rule::in(['semanal', 'quinzenal', 'mensal'])],
            'notes' => ['nullable', 'string'],
        ]);
        $case->update($data);

        return $this->success($case->fresh(['client:id,name']));
    }

    public function destroy(Request $request, ProfessionalCase $case)
    {
        $this->authorizeCase($request, $case);
        $case->delete();

        return $this->success(null, 'Caso excluido.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeCase(Request $request, ProfessionalCase $case): void
    {
        abort_if($case->company_id !== $this->companyId($request), 403);
    }
}
