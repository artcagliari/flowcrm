<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'title', 'body', 'type', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];
}
