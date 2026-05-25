<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'subject_type', 'subject_id', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array'];
}
