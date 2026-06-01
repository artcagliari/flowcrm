<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Task;

class CrmOverdueMarker
{
    public function markForCompany(int $companyId): void
    {
        Payment::where('company_id', $companyId)
            ->where('status', 'pendente')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'atrasado']);

        Expense::where('company_id', $companyId)
            ->where('status', 'pendente')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'atrasado']);

        Task::where('company_id', $companyId)
            ->whereIn('status', ['pendente', 'em andamento'])
            ->where(function ($query) {
                $query->where('due_at', '<', now())
                    ->orWhereDate('due_date', '<', today());
            })
            ->update(['status' => 'atrasada']);
    }
}
