<?php

namespace App\Http\Requests\Api;

class StorePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'description' => ['required', 'string', 'max:190'],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
