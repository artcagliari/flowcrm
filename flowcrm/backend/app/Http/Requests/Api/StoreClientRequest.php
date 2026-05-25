<?php

namespace App\Http\Requests\Api;

class StoreClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'document' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'profession' => ['nullable', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
