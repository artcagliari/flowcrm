<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'name', 'color'];
}
