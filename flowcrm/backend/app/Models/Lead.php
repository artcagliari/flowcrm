<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'owner_id', 'pipeline_id', 'lead_stage_id', 'name', 'phone', 'whatsapp', 'email',
        'origin', 'interest', 'temperature', 'score', 'status', 'estimated_value', 'lost_reason',
        'lost_reason_id', 'notes', 'last_interaction_at', 'next_action_at', 'stage_entered_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'score' => 'integer',
        'last_interaction_at' => 'datetime',
        'next_action_at' => 'datetime',
        'stage_entered_at' => 'datetime',
    ];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function pipeline() { return $this->belongsTo(Pipeline::class); }
    public function stage() { return $this->belongsTo(LeadStage::class, 'lead_stage_id'); }
    public function lostReason() { return $this->belongsTo(LossReason::class); }
    public function deals() { return $this->hasMany(Deal::class); }
    public function stageHistories() { return $this->hasMany(LeadStageHistory::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function notes() { return $this->hasMany(Note::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function activities() { return $this->hasMany(Activity::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
}
