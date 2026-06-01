<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;

class AdminDashboardController extends Controller
{
    use RespondsWithJson;

    public function __invoke()
    {
        return $this->success([
            'stats' => [
                'companies' => Company::count(),
                'active_companies' => Company::where('status', 'active')->count(),
                'inactive_companies' => Company::where('status', 'inactive')->count(),
                'suspended_companies' => Company::where('status', 'suspended')->count(),
                'expiring_companies' => Company::whereNotNull('expires_at')->whereBetween('expires_at', [today(), today()->addDays(30)])->count(),
                'company_users' => User::where('role', '!=', 'super_admin')->count(),
                'expected_revenue' => 0,
            ],
            'latest_companies' => Company::withCount('users')->latest()->limit(8)->get(),
        ]);
    }
}
