<?php

namespace App\Http\Requests\Api;

class StoreNoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('content') && ! $this->filled('body')) {
            $this->merge(['body' => $this->input('content')]);
        }
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:40'],
            'is_private' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ];
    }
}
