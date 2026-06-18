<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;

class LeadScorer
{
    public function score(Lead $lead): int
    {
        $score = match ($lead->temperature) {
            'quente' => 40,
            'morno' => 25,
            default => 10,
        };

        if ($lead->estimated_value >= 10000) {
            $score += 20;
        } elseif ($lead->estimated_value >= 1000) {
            $score += 10;
        }

        if ($lead->last_interaction_at) {
            $days = Carbon::parse($lead->last_interaction_at)->diffInDays(now());
            $score += match (true) {
                $days <= 3 => 20,
                $days <= 7 => 10,
                $days <= 14 => 5,
                default => -10,
            };
        } else {
            $score -= 5;
        }

        $overdueTasks = Task::where('lead_id', $lead->id)
            ->whereNotIn('status', ['concluida'])
            ->where(fn ($q) => $q->where('status', 'atrasada')->orWhereDate('due_date', '<', today()))
            ->count();

        $score -= min(20, $overdueTasks * 5);

        if ($lead->whatsapp) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }

    public function refresh(Lead $lead): Lead
    {
        $lead->update(['score' => $this->score($lead)]);

        return $lead->fresh();
    }
}
