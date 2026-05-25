<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Task;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use RespondsWithJson;

    public function __invoke(Request $request)
    {
        $companyId = $request->attributes->get('current_company')->id;

        return $this->success([
            'clients_by_status' => Client::where('company_id', $companyId)->selectRaw('status as name, count(*) as value')->groupBy('status')->get(),
            'leads_by_origin' => Lead::where('company_id', $companyId)->selectRaw('origin as name, count(*) as value')->groupBy('origin')->get(),
            'tasks_done' => Task::where('company_id', $companyId)->where('status', 'concluída')->count(),
            'monthly_revenue' => collect(range(1, 12))->map(fn ($month) => ['month' => $month, 'value' => (float) Payment::where('company_id', $companyId)->whereMonth('paid_at', $month)->sum('amount')]),
        ]);
    }
}
