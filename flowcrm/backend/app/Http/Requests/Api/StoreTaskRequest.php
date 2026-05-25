<?php

namespace App\Http\Requests\Api;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ];
    }
}
