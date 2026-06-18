<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\LossReason;
use Illuminate\Http\Request;

class LossReasonController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(LossReason::where('company_id', $this->companyId($request))->orderBy('position')->get());
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'position' => ['nullable', 'integer']]);

        return $this->success(LossReason::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'position' => $data['position'] ?? LossReason::where('company_id', $companyId)->max('position') + 1,
        ]), 'Motivo criado.', 201);
    }

    public function update(Request $request, LossReason $lossReason)
    {
        $this->authorizeReason($request, $lossReason);
        $data = $request->validate(['name' => ['sometimes', 'string', 'max:120'], 'position' => ['nullable', 'integer']]);
        $lossReason->update($data);

        return $this->success($lossReason);
    }

    public function destroy(Request $request, LossReason $lossReason)
    {
        $this->authorizeReason($request, $lossReason);
        $lossReason->delete();

        return $this->success(null, 'Motivo excluido.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeReason(Request $request, LossReason $lossReason): void
    {
        abort_if($lossReason->company_id !== $this->companyId($request), 403);
    }
}
