<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $companyId = $request->attributes->get('current_company')->id;
        $from = $request->query('from');
        $to = $request->query('to');

        return $this->success([
            'overview' => [
                'clients' => $this->dateRange(Client::where('company_id', $companyId), 'created_at', $from, $to)->count(),
                'leads' => $this->dateRange(Lead::where('company_id', $companyId), 'created_at', $from, $to)->count(),
                'appointments' => $this->dateRange(Appointment::where('company_id', $companyId), 'starts_at', $from, $to)->count(),
                'pending_tasks' => $this->dateRange(Task::where('company_id', $companyId), 'created_at', $from, $to)->where('status', 'pendente')->count(),
                'revenue' => (float) $this->dateRange(Payment::where('company_id', $companyId), 'paid_at', $from, $to)->where('status', 'pago')->sum('amount'),
                'expenses' => (float) $this->dateRange(Expense::where('company_id', $companyId), 'paid_at', $from, $to)->where('status', 'pago')->sum('amount'),
            ],
            'clients' => [
                'by_status' => $this->groupCount(Client::where('company_id', $companyId), 'status', 'created_at', $from, $to),
                'by_origin' => $this->groupCount(Client::where('company_id', $companyId), 'origin', 'created_at', $from, $to),
                'by_city' => $this->groupCount(Client::where('company_id', $companyId), 'city', 'created_at', $from, $to, 8),
                'new_by_month' => $this->monthlyCount(Client::where('company_id', $companyId), 'created_at', $from, $to),
            ],
            'leads' => [
                'by_status' => $this->groupCount(Lead::where('company_id', $companyId), 'status', 'created_at', $from, $to),
                'by_origin' => $this->groupCount(Lead::where('company_id', $companyId), 'origin', 'created_at', $from, $to),
                'by_temperature' => $this->groupCount(Lead::where('company_id', $companyId), 'temperature', 'created_at', $from, $to),
                'estimated_by_stage' => $this->groupSum(Lead::where('company_id', $companyId), 'status', 'estimated_value', 'created_at', $from, $to),
                'conversion_rate' => $this->conversionRate($companyId, $from, $to),
            ],
            'finance' => [
                'monthly_revenue' => $this->monthlySum(Payment::where('company_id', $companyId)->where('status', 'pago'), 'paid_at', 'amount', $from, $to),
                'monthly_expenses' => $this->monthlySum(Expense::where('company_id', $companyId)->where('status', 'pago'), 'paid_at', 'amount', $from, $to),
                'payments_by_status' => $this->groupCount(Payment::where('company_id', $companyId), 'status', 'created_at', $from, $to),
                'expenses_by_category' => $this->groupSum(Expense::where('company_id', $companyId), 'category', 'amount', 'created_at', $from, $to),
                'pending_payments' => (float) $this->dateRange(Payment::where('company_id', $companyId), 'due_date', $from, $to)->where('status', 'pendente')->sum('amount'),
                'overdue_payments' => (float) Payment::where('company_id', $companyId)->where('status', 'atrasado')->sum('amount'),
            ],
            'appointments' => [
                'by_status' => $this->groupCount(Appointment::where('company_id', $companyId), 'status', 'starts_at', $from, $to),
                'by_type' => $this->groupCount(Appointment::where('company_id', $companyId), 'type', 'starts_at', $from, $to),
                'by_month' => $this->monthlyCount(Appointment::where('company_id', $companyId), 'starts_at', $from, $to),
                'upcoming' => Appointment::where('company_id', $companyId)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(6)->get(['id', 'title', 'type', 'status', 'starts_at']),
            ],
            'tasks' => [
                'by_status' => $this->groupCount(Task::where('company_id', $companyId), 'status', 'created_at', $from, $to),
                'by_priority' => $this->groupCount(Task::where('company_id', $companyId), 'priority', 'created_at', $from, $to),
                'completed_by_month' => $this->monthlyCount(Task::where('company_id', $companyId)->whereIn('status', ['concluida']), 'updated_at', $from, $to),
                'overdue' => Task::where('company_id', $companyId)->whereNotIn('status', ['concluida'])->where('due_at', '<', now())->count(),
            ],
        ]);
    }

    private function dateRange($query, string $column, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($query) => $query->whereDate($column, '>=', $from))
            ->when($to, fn ($query) => $query->whereDate($column, '<=', $to));
    }

    private function groupCount($query, string $groupColumn, string $dateColumn, ?string $from, ?string $to, int $limit = 12)
    {
        return $this->dateRange($query, $dateColumn, $from, $to)
            ->selectRaw("coalesce({$groupColumn}, 'Nao informado') as name, count(*) as value")
            ->groupBy($groupColumn)
            ->orderByDesc('value')
            ->limit($limit)
            ->get();
    }

    private function groupSum($query, string $groupColumn, string $sumColumn, string $dateColumn, ?string $from, ?string $to, int $limit = 12)
    {
        return $this->dateRange($query, $dateColumn, $from, $to)
            ->selectRaw("coalesce({$groupColumn}, 'Nao informado') as name, sum({$sumColumn}) as value")
            ->groupBy($groupColumn)
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => ['name' => $item->name, 'value' => (float) $item->value]);
    }

    private function monthlyCount($query, string $dateColumn, ?string $from, ?string $to)
    {
        return $this->dateRange($query, $dateColumn, $from, $to)
            ->whereNotNull($dateColumn)
            ->get([$dateColumn])
            ->groupBy(fn ($item) => Carbon::parse($item->{$dateColumn})->format('Y-m'))
            ->map(fn ($items, $month) => ['name' => $month, 'value' => $items->count()])
            ->values();
    }

    private function monthlySum($query, string $dateColumn, string $sumColumn, ?string $from, ?string $to)
    {
        return $this->dateRange($query, $dateColumn, $from, $to)
            ->whereNotNull($dateColumn)
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->{$dateColumn})->format('Y-m'))
            ->map(fn ($items, $month) => ['name' => $month, 'value' => (float) $items->sum($sumColumn)])
            ->values();
    }

    private function conversionRate(int $companyId, ?string $from, ?string $to): float
    {
        $total = $this->dateRange(Lead::where('company_id', $companyId), 'created_at', $from, $to)->count();
        $converted = $this->dateRange(Lead::where('company_id', $companyId), 'created_at', $from, $to)->where('status', 'convertido')->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }
}
