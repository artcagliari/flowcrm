<?php

namespace App\Http\Requests\Api;

class StoreNoteRequest extends FormRequest
{
    public function rules(): array
    {
        return ['body' => ['required', 'string'], 'client_id' => ['nullable', 'exists:clients,id'], 'lead_id' => ['nullable', 'exists:leads,id']];
    }
}
