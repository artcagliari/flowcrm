<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenceEnrollment extends Model
{
    protected $fillable = ['sequence_id', 'lead_id', 'client_id', 'current_step', 'status', 'next_run_at'];

    protected $casts = ['next_run_at' => 'datetime', 'current_step' => 'integer'];

    public function sequence() { return $this->belongsTo(FollowUpSequence::class, 'sequence_id'); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
}
