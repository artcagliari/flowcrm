<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'client_id', 'lead_id', 'body'];
}
