<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanLimiter
{
    public function canAddUser(Company $company): bool
    {
        $max = $this->maxUsers($company);

        if ($max === null) {
            return true;
        }

        $count = DB::table('company_user')->where('company_id', $company->id)->count();

        return $count < $max;
    }

    public function canAddLead(Company $company): bool
    {
        $max = $this->maxLeads($company);

        if ($max === null) {
            return true;
        }

        return Lead::where('company_id', $company->id)->count() < $max;
    }

    public function maxUsers(Company $company): ?int
    {
        if ($company->max_users) {
            return (int) $company->max_users;
        }

        return $this->planFor($company)?->max_users;
    }

    public function maxLeads(Company $company): ?int
    {
        return $this->planFor($company)?->max_leads;
    }

    private function planFor(Company $company): ?Plan
    {
        $subscription = Subscription::where('company_id', $company->id)
            ->whereIn('status', ['active', 'trial'])
            ->latest()
            ->first();

        return $subscription?->plan;
    }
}
