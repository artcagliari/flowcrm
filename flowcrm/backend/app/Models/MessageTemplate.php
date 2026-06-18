<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageTemplate extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'channel', 'subject', 'body'];
}
