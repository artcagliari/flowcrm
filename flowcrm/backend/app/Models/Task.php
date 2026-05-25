<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'owner_id', 'client_id', 'lead_id', 'title', 'description', 'due_at', 'priority', 'status'];

    protected $casts = ['due_at' => 'datetime'];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
}
