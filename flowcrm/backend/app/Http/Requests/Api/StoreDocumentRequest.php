<?php

namespace App\Http\Requests\Api;

class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return ['file' => ['required', 'file', 'max:10240'], 'category' => ['nullable', 'string', 'max:60'], 'client_id' => ['nullable', 'exists:clients,id'], 'lead_id' => ['nullable', 'exists:leads,id']];
    }
}
