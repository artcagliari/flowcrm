<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'type', 'setting_key', 'setting_value'];

    protected $casts = ['setting_value' => 'array'];
}
