<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'title', 'message', 'body', 'type', 'read_at', 'action_url'];

    protected $casts = ['read_at' => 'datetime'];
}
