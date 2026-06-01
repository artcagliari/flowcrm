<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'owner_id', 'client_id', 'lead_id', 'title', 'description', 'due_date', 'due_at', 'priority', 'status', 'completed_at'];

    protected $casts = ['due_date' => 'date', 'due_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
}
