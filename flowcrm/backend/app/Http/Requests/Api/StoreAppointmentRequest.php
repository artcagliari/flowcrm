<?php

namespace App\Http\Requests\Api;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'reminder_at' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
