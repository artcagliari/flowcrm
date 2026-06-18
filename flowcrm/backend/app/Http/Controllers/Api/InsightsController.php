<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Services\CrmInsightsService;
use Illuminate\Http\Request;

class InsightsController extends Controller
{
    use RespondsWithJson;

    public function __construct(private CrmInsightsService $insights) {}

    public function client(Request $request, Client $client)
    {
        abort_if($client->company_id !== $this->companyId($request), 403);

        return $this->success($this->insights->forClient($client));
    }

    public function lead(Request $request, Lead $lead)
    {
        abort_if($lead->company_id !== $this->companyId($request), 403);

        return $this->success($this->insights->forLead($lead));
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
