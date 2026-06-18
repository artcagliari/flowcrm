<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;

class MyDashboardController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $companyId = $this->companyId($request);
        $userId = $request->user()->id;

        $myLeads = Lead::where('company_id', $companyId)->where('owner_id', $userId)->whereNotIn('status', ['convertido', 'perdido'])->count();
        $myTasks = Task::where('company_id', $companyId)->where('owner_id', $userId)->whereNotIn('status', ['concluida'])->count();
        $myDeals = Deal::where('company_id', $companyId)->where('owner_id', $userId)->where('status', 'aberto')->count();
        $forecast = (float) Deal::where('company_id', $companyId)->where('owner_id', $userId)->where('status', 'aberto')
            ->get()->sum(fn (Deal $d) => $d->weightedValue());

        return $this->success([
            'stats' => [
                'my_leads' => $myLeads,
                'my_tasks' => $myTasks,
                'my_deals' => $myDeals,
                'my_forecast' => $forecast,
            ],
            'urgent_tasks' => Task::where('company_id', $companyId)->where('owner_id', $userId)
                ->whereNotIn('status', ['concluida'])
                ->orderBy('due_at')
                ->limit(5)
                ->get(),
            'hot_leads' => Lead::where('company_id', $companyId)->where('owner_id', $userId)
                ->whereNotIn('status', ['convertido', 'perdido'])
                ->orderByDesc('score')
                ->limit(5)
                ->get(['id', 'name', 'score', 'temperature', 'next_action_at']),
            'next_actions' => $this->nextActions($companyId, $userId),
        ]);
    }

    private function nextActions(int $companyId, int $userId): array
    {
        $actions = [];

        Lead::where('company_id', $companyId)->where('owner_id', $userId)
            ->whereNotIn('status', ['convertido', 'perdido'])
            ->where(fn ($q) => $q->whereNull('last_interaction_at')->orWhere('last_interaction_at', '<', now()->subDays(7)))
            ->limit(3)
            ->get()
            ->each(fn ($lead) => $actions[] = ['type' => 'lead', 'id' => $lead->id, 'title' => "Retomar contato: {$lead->name}", 'priority' => 'alta']);

        Task::where('company_id', $companyId)->where('owner_id', $userId)
            ->whereNotIn('status', ['concluida'])
            ->whereDate('due_date', '<=', today())
            ->limit(3)
            ->get()
            ->each(fn ($task) => $actions[] = ['type' => 'task', 'id' => $task->id, 'title' => "Tarefa vencida: {$task->title}", 'priority' => 'urgente']);

        return $actions;
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
