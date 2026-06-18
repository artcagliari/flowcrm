<?php

namespace App\Http\Requests\Api;

class StoreAppointmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->filled('user_id') && ! $this->filled('owner_id')) {
            $data['owner_id'] = $this->input('user_id');
        }
        if ($this->filled('start_at') && ! $this->filled('starts_at')) {
            $data['starts_at'] = $this->input('start_at');
        }
        if ($this->filled('end_at') && ! $this->filled('ends_at')) {
            $data['ends_at'] = $this->input('end_at');
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
            'type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'reminder_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'exists:users,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
