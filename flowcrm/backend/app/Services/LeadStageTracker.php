<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadStageHistory;
use Illuminate\Http\Request;

class LeadStageTracker
{
    public function recordMove(Lead $lead, ?int $fromStageId, int $toStageId, ?int $userId = null): void
    {
        if ($fromStageId) {
            LeadStageHistory::where('lead_id', $lead->id)
                ->where('to_stage_id', $fromStageId)
                ->whereNull('left_at')
                ->latest()
                ->first()
                ?->update(['left_at' => now()]);
        }

        LeadStageHistory::create([
            'lead_id' => $lead->id,
            'from_stage_id' => $fromStageId,
            'to_stage_id' => $toStageId,
            'user_id' => $userId,
            'entered_at' => now(),
        ]);

        $lead->update(['stage_entered_at' => now()]);
    }
}
