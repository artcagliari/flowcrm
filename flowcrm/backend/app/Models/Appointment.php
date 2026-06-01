<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'owner_id', 'client_id', 'lead_id', 'title', 'description', 'start_at', 'end_at', 'starts_at', 'ends_at', 'location', 'type', 'status', 'reminder_at', 'notes'];

    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'reminder_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function client() { return $this->belongsTo(Client::class); }
}
