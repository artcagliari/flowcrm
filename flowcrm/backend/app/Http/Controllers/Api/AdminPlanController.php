<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    use RespondsWithJson;

    public function index()
    {
        return $this->success(Plan::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->success(Plan::create($data), 'Plano criado com sucesso.', 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $plan->update($data);

        return $this->success($plan->fresh(), 'Plano atualizado com sucesso.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return $this->success(null, 'Plano excluido com sucesso.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
        ]);
    }
}
