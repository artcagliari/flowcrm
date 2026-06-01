<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'owner_id', 'lead_stage_id', 'name', 'phone', 'whatsapp', 'email', 'origin', 'interest', 'temperature', 'status', 'estimated_value', 'lost_reason', 'notes', 'last_interaction_at', 'next_action_at'];

    protected $casts = ['estimated_value' => 'decimal:2', 'last_interaction_at' => 'datetime', 'next_action_at' => 'datetime'];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function stage() { return $this->belongsTo(LeadStage::class, 'lead_stage_id'); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function notes() { return $this->hasMany(Note::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function activities() { return $this->hasMany(Activity::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
}
