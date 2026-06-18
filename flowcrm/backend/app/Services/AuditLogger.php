<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(Request $request, string $action, ?Model $entity = null, ?array $old = null, ?array $new = null): void
    {
        $company = $request->attributes->get('current_company');

        if (! $company) {
            return;
        }

        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id' => $entity?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request->ip(),
        ]);
    }
}
