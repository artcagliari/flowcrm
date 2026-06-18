<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LossReason extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'position'];

    protected $casts = ['position' => 'integer'];
}
