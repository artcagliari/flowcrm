<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Automation extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'trigger_type', 'trigger_config', 'action_type', 'action_config', 'is_active'];

    protected $casts = [
        'trigger_config' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
    ];
}
