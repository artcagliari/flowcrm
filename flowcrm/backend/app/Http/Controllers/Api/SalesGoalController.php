<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Payment;
use App\Models\SalesGoal;
use Illuminate\Http\Request;

class SalesGoalController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $goals = SalesGoal::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->with('user:id,name')
            ->get()
            ->map(function (SalesGoal $goal) use ($companyId, $year, $month) {
                $revenue = (float) Payment::where('company_id', $companyId)
                    ->where('status', 'pago')
                    ->whereMonth('paid_at', $month)
                    ->whereYear('paid_at', $year)
                    ->whereHas('client', fn ($q) => $q->where('owner_id', $goal->user_id))
                    ->sum('amount');

                $dealsWon = Deal::where('company_id', $companyId)
                    ->where('owner_id', $goal->user_id)
                    ->where('status', 'ganho')
                    ->whereMonth('won_at', $month)
                    ->whereYear('won_at', $year)
                    ->count();

                return [
                    ...$goal->toArray(),
                    'achieved_amount' => $revenue,
                    'achieved_deals' => $dealsWon,
                    'amount_progress' => $goal->target_amount > 0 ? round(($revenue / (float) $goal->target_amount) * 100, 1) : 0,
                    'deals_progress' => $goal->target_deals > 0 ? round(($dealsWon / $goal->target_deals) * 100, 1) : 0,
                ];
            });

        return $this->success($goals);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'target_deals' => ['nullable', 'integer', 'min:0'],
        ]);

        $goal = SalesGoal::updateOrCreate(
            ['company_id' => $this->companyId($request), 'user_id' => $data['user_id'], 'year' => $data['year'], 'month' => $data['month']],
            ['target_amount' => $data['target_amount'] ?? 0, 'target_deals' => $data['target_deals'] ?? 0]
        );

        return $this->success($goal->load('user:id,name'), 'Meta salva.', 201);
    }

    public function destroy(Request $request, SalesGoal $goal)
    {
        abort_if($goal->company_id !== $this->companyId($request), 403);
        $goal->delete();

        return $this->success(null, 'Meta excluida.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
