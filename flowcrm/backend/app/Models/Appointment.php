<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'owner_id', 'client_id', 'lead_id', 'title', 'type', 'status', 'starts_at', 'ends_at', 'reminder_at', 'notes'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'reminder_at' => 'datetime'];
}
