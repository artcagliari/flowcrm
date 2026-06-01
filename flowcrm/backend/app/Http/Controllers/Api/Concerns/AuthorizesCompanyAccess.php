<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait AuthorizesCompanyAccess
{
    protected function currentRole(Request $request): ?string
    {
        $company = $request->attributes->get('current_company');

        if (! $company || ! $request->user()) {
            return null;
        }

        return DB::table('company_user')
            ->where('company_id', $company->id)
            ->where('user_id', $request->user()->id)
            ->value('role') ?: $request->user()->role;
    }

    protected function canManageModule(Request $request, string $module): bool
    {
        if ($request->user()?->isSuperAdmin()) {
            return true;
        }

        $role = $this->currentRole($request);

        return match ($role) {
            'company_admin' => true,
            'employee' => in_array($module, ['clients', 'leads', 'tasks', 'appointments', 'documents', 'notes'], true),
            'financial' => in_array($module, ['payments', 'expenses', 'documents', 'notes'], true),
            default => false,
        };
    }

    protected function abortUnlessCanManageModule(Request $request, string $module): void
    {
        abort_unless($this->canManageModule($request, $module), 403, 'Usuario sem permissao para alterar este modulo.');
    }

    protected function moduleForRecord(Model|string $record): string
    {
        $class = is_string($record) ? class_basename($record) : class_basename(get_class($record));

        return match ($class) {
            'Client' => 'clients',
            'Lead' => 'leads',
            'Task' => 'tasks',
            'Appointment' => 'appointments',
            'Payment' => 'payments',
            'Expense' => 'expenses',
            'Document' => 'documents',
            'Note' => 'notes',
            default => strtolower($class),
        };
    }
}
