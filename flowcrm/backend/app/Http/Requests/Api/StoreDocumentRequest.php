<?php

namespace App\Http\Requests\Api;

class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,png,jpg,jpeg,webp,zip,rar', 'max:10240'],
            'category' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ];
    }
}
