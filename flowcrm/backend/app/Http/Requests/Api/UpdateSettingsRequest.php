<?php

namespace App\Http\Requests\Api;

class UpdateSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return ['settings' => ['required', 'array']];
    }
}
