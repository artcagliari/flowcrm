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
use App\Services\CrmOverdueMarker;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $company = $request->attributes->get('current_company');
        $companyId = $company->id;
        $mode = $company->profession_mode ?? 'empresa';

        app(CrmOverdueMarker::class)->markForCompany($companyId);
        $month = now()->month;
        $year = now()->year;

        $revenue = Payment::where('company_id', $companyId)
            ->where('status', 'pago')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->sum('amount');

        $expenses = Expense::where('company_id', $companyId)
            ->where('status', 'pago')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->sum('amount');

        $openContactStatuses = ['encaminhado', 'descartado'];

        return $this->success([
            'profession_mode' => $mode,
            'stats' => [
                'clients' => Client::where('company_id', $companyId)->count(),
                'active_clients' => Client::where('company_id', $companyId)->whereIn('status', ['encaminhado', 'ativo', 'em_atendimento', 'agendado'])->count(),
                'leads' => Lead::where('company_id', $companyId)->count(),
                'open_contacts' => Lead::where('company_id', $companyId)->whereNotIn('status', $openContactStatuses)->count(),
                'active_cases' => Appointment::where('company_id', $companyId)
                    ->whereIn('status', ['agendado', 'confirmado'])
                    ->where(fn ($q) => $q->where('starts_at', '>=', now())->orWhere('start_at', '>=', now()))
                    ->count(),
                'pending_tasks' => Task::where('company_id', $companyId)->whereIn('status', ['pendente', 'em andamento'])->count(),
                'late_tasks' => Task::where('company_id', $companyId)
                    ->where(function ($query) {
                        $query->where('status', 'atrasada')
                            ->orWhere(fn ($q) => $q->whereDate('due_date', '<', today())->whereNotIn('status', ['concluida']));
                    })
                    ->count(),
                'today_appointments' => Appointment::where('company_id', $companyId)
                    ->where(fn ($q) => $q->whereDate('start_at', today())->orWhereDate('starts_at', today()))
                    ->count(),
                'pending_payments' => Payment::where('company_id', $companyId)->where('status', 'pendente')->count(),
                'late_payments' => Payment::where('company_id', $companyId)
                    ->where(fn ($q) => $q->where('status', 'atrasado')->orWhere(fn ($late) => $late->whereDate('due_date', '<', today())->where('status', 'pendente')))
                    ->count(),
                'monthly_revenue' => (float) $revenue,
                'monthly_expenses' => (float) $expenses,
                'estimated_profit' => (float) ($revenue - $expenses),
            ],
            'recent_activities' => Activity::where('company_id', $companyId)->latest()->limit(8)->get(),
            'upcoming_appointments' => Appointment::where('company_id', $companyId)
                ->where(fn ($q) => $q->where('start_at', '>=', now())->orWhere('starts_at', '>=', now()))
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'urgent_tasks' => Task::where('company_id', $companyId)
                ->whereIn('priority', ['alta', 'urgente'])
                ->whereNotIn('status', ['concluida'])
                ->orderBy('due_at')
                ->limit(5)
                ->get(),
            'recent_payments' => Payment::where('company_id', $companyId)
                ->latest()
                ->limit(5)
                ->get(),
            'pending_contacts' => Lead::where('company_id', $companyId)
                ->whereNotIn('status', $openContactStatuses)
                ->latest('last_interaction_at')
                ->limit(5)
                ->get(['id', 'name', 'phone', 'whatsapp', 'status', 'last_interaction_at']),
            'monthly_revenue_chart' => collect(range(1, 12))->map(fn ($chartMonth) => [
                'month' => str_pad((string) $chartMonth, 2, '0', STR_PAD_LEFT),
                'value' => (float) Payment::where('company_id', $companyId)
                    ->where('status', 'pago')
                    ->whereMonth('paid_at', $chartMonth)
                    ->whereYear('paid_at', $year)
                    ->sum('amount'),
            ]),
            'clients_by_status' => Client::where('company_id', $companyId)
                ->selectRaw('status as name, count(*) as value')
                ->groupBy('status')
                ->get(),
        ]);
    }
}
