<?php

namespace App\Http\Requests\Api;

class StoreTaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->filled('user_id') && ! $this->filled('owner_id')) {
            $data['owner_id'] = $this->input('user_id');
        }
        if ($this->filled('due_date') && ! $this->filled('due_at')) {
            $data['due_at'] = $this->input('due_date');
        }
        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'completed_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'exists:users,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ];
    }
}
