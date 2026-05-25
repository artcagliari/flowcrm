<?php

namespace App\Http\Requests\Api;

class UpdateCompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:160'], 'email' => ['nullable', 'email'], 'phone' => ['nullable', 'string', 'max:32'], 'primary_color' => ['nullable', 'string', 'max:16']];
    }
}
