<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpSequenceStep extends Model
{
    protected $fillable = ['sequence_id', 'position', 'delay_days', 'action_type', 'action_config'];

    protected $casts = ['action_config' => 'array', 'position' => 'integer', 'delay_days' => 'integer'];

    public function sequence()
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }
}
