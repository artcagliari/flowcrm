<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StoreAdminCompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company.name' => ['required', 'string', 'max:160'],
            'company.legal_name' => ['nullable', 'string', 'max:255'],
            'company.document' => ['nullable', 'string', 'max:32'],
            'company.email' => ['nullable', 'email', 'max:255'],
            'company.phone' => ['nullable', 'string', 'max:32'],
            'company.whatsapp' => ['nullable', 'string', 'max:32'],
            'company.city' => ['nullable', 'string', 'max:120'],
            'company.state' => ['nullable', 'string', 'max:80'],
            'company.address' => ['nullable', 'string', 'max:255'],
            'company.zip_code' => ['nullable', 'string', 'max:20'],
            'company.type' => ['required', Rule::in(['company', 'autonomous'])],
            'company.status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'company.plan_name' => ['nullable', 'string', 'max:120'],
            'company.max_users' => ['nullable', 'integer', 'min:1'],
            'company.starts_at' => ['nullable', 'date'],
            'company.expires_at' => ['nullable', 'date', 'after_or_equal:company.starts_at'],
            'company.notes' => ['nullable', 'string'],
            'admin.name' => ['required', 'string', 'max:160'],
            'admin.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin.password' => ['required', 'string', 'min:8', 'confirmed'],
            'admin.phone' => ['nullable', 'string', 'max:32'],
        ];
    }
}
