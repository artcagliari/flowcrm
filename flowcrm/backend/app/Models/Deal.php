<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'pipeline_id', 'lead_stage_id', 'lead_id', 'client_id', 'owner_id',
        'title', 'value', 'probability', 'expected_close_date', 'status',
        'lost_reason_id', 'notes', 'won_at', 'lost_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    public function pipeline() { return $this->belongsTo(Pipeline::class); }
    public function stage() { return $this->belongsTo(LeadStage::class, 'lead_stage_id'); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function lostReason() { return $this->belongsTo(LossReason::class); }

    public function weightedValue(): float
    {
        return (float) $this->value * ($this->probability / 100);
    }
}
