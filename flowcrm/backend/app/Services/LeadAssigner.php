<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadAssigner
{
    public function assignOwner(Company $company, ?int $requestedOwnerId = null): ?int
    {
        if ($requestedOwnerId) {
            return $requestedOwnerId;
        }

        if ($company->assignment_mode !== 'round_robin') {
            return null;
        }

        $userIds = DB::table('company_user')
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'employee'])
            ->orderBy('user_id')
            ->pluck('user_id')
            ->all();

        if ($userIds === []) {
            return null;
        }

        $lastId = $company->last_assigned_user_id;
        $index = $lastId ? array_search($lastId, $userIds, true) : false;
        $nextIndex = $index === false ? 0 : ($index + 1) % count($userIds);
        $nextUserId = (int) $userIds[$nextIndex];

        $company->update(['last_assigned_user_id' => $nextUserId]);

        return $nextUserId;
    }
}
