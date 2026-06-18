<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStageHistory extends Model
{
    protected $fillable = ['lead_id', 'from_stage_id', 'to_stage_id', 'user_id', 'entered_at', 'left_at'];

    protected $casts = ['entered_at' => 'datetime', 'left_at' => 'datetime'];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function fromStage() { return $this->belongsTo(LeadStage::class, 'from_stage_id'); }
    public function toStage() { return $this->belongsTo(LeadStage::class, 'to_stage_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
