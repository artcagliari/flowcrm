<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use Illuminate\Http\Request;

class LeadStageController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $companyId = $request->attributes->get('current_company')->id;

        return $this->success(
            LeadStage::where('company_id', $companyId)
                ->orderBy('position')
                ->get(['id', 'pipeline_id', 'name', 'position', 'color', 'is_won', 'is_lost'])
        );
    }
}
