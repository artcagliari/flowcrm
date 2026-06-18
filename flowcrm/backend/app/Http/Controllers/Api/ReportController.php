<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadStageHistory;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
                'by_stage' => $this->stageCount($companyId, $from, $to),
                'by_origin' => $this->groupCount(Lead::where('company_id', $companyId), 'origin', 'created_at', $from, $to),
                'by_temperature' => $this->groupCount(Lead::where('company_id', $companyId), 'temperature', 'created_at', $from, $to),
                'estimated_by_stage' => $this->stageSum($companyId, 'estimated_value', $from, $to),
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
            'pipeline' => [
                'funnel_conversion' => $this->funnelConversion($companyId, $from, $to),
                'avg_time_in_stage_hours' => $this->avgTimeInStage($companyId),
                'forecast' => $this->forecast($companyId),
            ],
            'sales' => [
                'seller_ranking' => $this->sellerRanking($companyId, $from, $to),
                'inactive_contacts' => $this->inactiveContacts($companyId),
            ],
        ]);
    }

    private function funnelConversion(int $companyId, ?string $from, ?string $to): array
    {
        $stages = $this->stageCount($companyId, $from, $to);
        $total = max(1, (int) collect($stages)->sum('value'));

        return collect($stages)->map(fn ($stage) => [
            'name' => $stage['name'],
            'count' => $stage['value'],
            'rate' => round(($stage['value'] / $total) * 100, 1),
        ])->values()->all();
    }

    private function avgTimeInStage(int $companyId): array
    {
        return LeadStageHistory::query()
            ->join('leads', 'lead_stage_histories.lead_id', '=', 'leads.id')
            ->leftJoin('lead_stages', 'lead_stage_histories.to_stage_id', '=', 'lead_stages.id')
            ->where('leads.company_id', $companyId)
            ->whereNotNull('lead_stage_histories.left_at')
            ->groupBy('lead_stage_histories.to_stage_id', 'lead_stages.name')
            ->selectRaw('coalesce(lead_stages.name, "Sem etapa") as name, avg(timestampdiff(HOUR, lead_stage_histories.entered_at, lead_stage_histories.left_at)) as hours')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'hours' => round((float) $row->hours, 1)])
            ->values()
            ->all();
    }

    private function forecast(int $companyId): array
    {
        $deals = Deal::where('company_id', $companyId)->where('status', 'aberto')->get();

        return [
            'total_value' => (float) $deals->sum('value'),
            'weighted_value' => (float) $deals->sum(fn (Deal $d) => $d->weightedValue()),
            'by_month' => $deals->groupBy(fn (Deal $d) => optional($d->expected_close_date)->format('Y-m') ?? 'sem_data')
                ->map(fn ($items, $month) => [
                    'name' => $month,
                    'value' => (float) $items->sum(fn (Deal $d) => $d->weightedValue()),
                ])->values()->all(),
        ];
    }

    private function sellerRanking(int $companyId, ?string $from, ?string $to): array
    {
        return User::query()
            ->select('users.id', 'users.name')
            ->selectRaw('(select count(*) from leads where leads.owner_id = users.id and leads.company_id = ? and leads.status = "convertido") as converted_leads', [$companyId])
            ->selectRaw('(select coalesce(sum(amount),0) from payments join clients on clients.id = payments.client_id where clients.owner_id = users.id and payments.company_id = ? and payments.status = "pago") as revenue', [$companyId])
            ->selectRaw('(select count(*) from deals where deals.owner_id = users.id and deals.company_id = ? and deals.status = "ganho") as won_deals', [$companyId])
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $companyId)
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'converted_leads' => (int) $u->converted_leads,
                'won_deals' => (int) $u->won_deals,
                'revenue' => (float) $u->revenue,
            ])
            ->all();
    }

    private function inactiveContacts(int $companyId): array
    {
        $threshold = now()->subDays(14);

        $inactiveLeads = Lead::where('company_id', $companyId)
            ->whereNotIn('status', ['convertido', 'perdido'])
            ->where(fn ($q) => $q->whereNull('last_interaction_at')->orWhere('last_interaction_at', '<', $threshold))
            ->orderBy('last_interaction_at')
            ->limit(10)
            ->get(['id', 'name', 'last_interaction_at', 'owner_id']);

        $inactiveClients = Client::where('company_id', $companyId)
            ->where('status', 'ativo')
            ->where(fn ($q) => $q->whereNull('last_contact_at')->orWhere('last_contact_at', '<', $threshold))
            ->orderBy('last_contact_at')
            ->limit(10)
            ->get(['id', 'name', 'last_contact_at', 'owner_id']);

        return [
            'leads' => $inactiveLeads,
            'clients' => $inactiveClients,
        ];
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

    private function stageBaseQuery(int $companyId, ?string $from, ?string $to)
    {
        return $this->dateRange(
            Lead::where('leads.company_id', $companyId)
                ->leftJoin('lead_stages', 'leads.lead_stage_id', '=', 'lead_stages.id'),
            'leads.created_at',
            $from,
            $to
        )->groupBy('leads.lead_stage_id', 'lead_stages.name', 'lead_stages.position')
            ->orderBy('lead_stages.position');
    }

    private function stageCount(int $companyId, ?string $from, ?string $to)
    {
        return $this->stageBaseQuery($companyId, $from, $to)
            ->selectRaw("coalesce(lead_stages.name, 'Sem etapa') as name, count(*) as value")
            ->get()
            ->map(fn ($item) => ['name' => $item->name, 'value' => (int) $item->value])
            ->values();
    }

    private function stageSum(int $companyId, string $sumColumn, ?string $from, ?string $to)
    {
        return $this->stageBaseQuery($companyId, $from, $to)
            ->selectRaw("coalesce(lead_stages.name, 'Sem etapa') as name, sum(leads.{$sumColumn}) as value")
            ->get()
            ->map(fn ($item) => ['name' => $item->name, 'value' => (float) $item->value])
            ->values();
    }

    private function conversionRate(int $companyId, ?string $from, ?string $to): float
    {
        $total = $this->dateRange(Lead::where('company_id', $companyId), 'created_at', $from, $to)->count();
        $converted = $this->dateRange(Lead::where('company_id', $companyId), 'created_at', $from, $to)->where('status', 'convertido')->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }
}
