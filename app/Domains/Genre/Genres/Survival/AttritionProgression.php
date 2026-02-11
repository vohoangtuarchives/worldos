<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\ProgressionRule;

class AttritionProgression implements ProgressionRule
{
    public function stages(): array
    {
        // Survival isn't about leveling up, it's about not dying.
        // Stages represent "Days Survived" or "Condition"
        return [
            1 => 'Healthy',
            2 => 'Fatigued',
            3 => 'Hungry',
            4 => 'Starving',
            5 => 'Delirious',
            6 => 'Critical',
            7 => 'Deceased'
        ];
        // Note: The logic here is inverted compared to Xianxia.
        // Higher stage = worse condition? Or Stages = Days Survived?
        // Let's stick to "Condition" as requested by "Attrition"
        // Actually, "Progression" usually implies forward movement. 
        // In survival, "Days Survived" is the score. 
        // But let's define "Stages" as "Milestones of Survival"
    }

    public function milestones(): array
    {
        return [
            'Day 1' => 'Survivor',
            'Day 3' => 'Scavenger',
            'Day 7' => 'Adapter',
            'Day 30' => 'Veteran',
            'Day 100' => 'Legend',
        ];
    }

    public function canSkipStages(): bool
    {
        return false; // You cannot skip days.
    }

    public function deathRisk(string $currentStage): float
    {
        // Risk increases over time/condition?
        // Let's say risk is constant but cumulative.
        return 0.1; // High constant risk
    }
}
