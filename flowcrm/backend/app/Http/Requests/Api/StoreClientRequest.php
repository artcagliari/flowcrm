<?php

namespace App\Http\Requests\Api;

class StoreClientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('user_id') && ! $this->filled('owner_id')) {
            $this->merge(['owner_id' => $this->input('user_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'document' => ['nullable', 'string', 'max:32'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:80'],
            'profession' => ['nullable', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
            'last_contact_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'exists:users,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
