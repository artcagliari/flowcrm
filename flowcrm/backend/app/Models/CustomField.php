<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomField extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'entity_type', 'name', 'field_type', 'options', 'is_required', 'position'];

    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'position' => 'integer'];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
