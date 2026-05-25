<?php

namespace App\Http\Requests\Api;

class StoreLeadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'origin' => ['nullable', 'string', 'max:120'],
            'interest' => ['nullable', 'string', 'max:190'],
            'temperature' => ['nullable', 'in:frio,morno,quente'],
            'status' => ['nullable', 'string', 'max:40'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'lead_stage_id' => ['nullable', 'exists:lead_stages,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
