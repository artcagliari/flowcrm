<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $query = AuditLog::where('company_id', $this->companyId($request))->with('user:id,name')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        return $this->success($query->paginate((int) $request->query('per_page', 30)));
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
