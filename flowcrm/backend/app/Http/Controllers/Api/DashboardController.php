<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $companyId = $request->attributes->get('current_company')->id;
        $revenue = Payment::where('company_id', $companyId)->whereMonth('paid_at', now()->month)->sum('amount');
        $expenses = Expense::where('company_id', $companyId)->whereMonth('paid_at', now()->month)->sum('amount');
        $converted = Lead::where('company_id', $companyId)->where('status', 'convertido')->count();
        $leadTotal = max(Lead::where('company_id', $companyId)->count(), 1);

        return $this->success([
            'stats' => [
                'clients' => Client::where('company_id', $companyId)->count(),
                'leads' => Lead::where('company_id', $companyId)->count(),
                'new_leads' => Lead::where('company_id', $companyId)->where('status', 'novo')->count(),
                'pending_tasks' => Task::where('company_id', $companyId)->where('status', 'pendente')->count(),
                'late_tasks' => Task::where('company_id', $companyId)->where('status', 'atrasada')->count(),
                'today_appointments' => Appointment::where('company_id', $companyId)->whereDate('starts_at', today())->count(),
                'monthly_revenue' => (float) $revenue,
                'monthly_expenses' => (float) $expenses,
                'estimated_profit' => (float) ($revenue - $expenses),
                'conversion_rate' => round(($converted / $leadTotal) * 100, 1),
            ],
            'recent_activities' => Activity::where('company_id', $companyId)->latest()->limit(8)->get(),
            'monthly_revenue_chart' => collect(range(1, 12))->map(fn ($month) => [
                'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'value' => (float) Payment::where('company_id', $companyId)->whereMonth('paid_at', $month)->sum('amount'),
            ]),
            'leads_by_origin' => Lead::where('company_id', $companyId)->selectRaw('origin as name, count(*) as value')->groupBy('origin')->get(),
        ]);
    }
}
