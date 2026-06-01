<?php

namespace App\Http\Requests\Api;

class StoreExpenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:190'],
            'user_id' => ['nullable', 'exists:users,id'],
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
