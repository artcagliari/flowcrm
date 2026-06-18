<?php

namespace App\Http\Requests\Api;

class UpdateClientRequest extends StoreClientRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['name'] = ['sometimes', 'required', 'string', 'max:190'];

        return $rules;
    }
}
