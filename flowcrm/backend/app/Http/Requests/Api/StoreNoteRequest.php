<?php

namespace App\Http\Requests\Api;

class StoreNoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('body') && ! $this->filled('content')) {
            $this->merge(['content' => $this->input('body')]);
        }
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:40'],
            'is_private' => ['nullable', 'boolean'],
            'sensitivity_level' => ['nullable', 'in:normal,sensitive'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ];
    }
}
