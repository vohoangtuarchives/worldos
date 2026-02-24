<?php

namespace WorldOS\Legacy\Application\Narrative\Dialogue\Services;

use WorldOS\Legacy\Application\Narrative\Character\Character;
use WorldOS\Legacy\Application\Narrative\Scene\Scene;
use Illuminate\Support\Collection;

class TurnScheduler
{
    /**
     * Determines which character should act next.
     * Logic:
     * 1. Calculate "Pressure" for each agent.
     *    Pressure = (Max Goal Priority / 100) + (Max Emotion Intensity)
     * 2. Pick agent with highest Pressure.
     * 3. Add randomness/noise to prevent deterministic loops.
     */
    public function pick(Scene $scene): ?Character
    {
        if ($scene->activeAgents->isEmpty()) {
            return null;
        }

        $candidates = $scene->activeAgents->map(function (Character $agent) {
            $goalPressure = $this->calculateGoalPressure($agent);
            $emotionPressure = $this->calculateEmotionPressure($agent);
            
            return [
                'agent' => $agent,
                'score' => $goalPressure + $emotionPressure + (mt_rand(0, 20) / 100) // 0.0 to 0.2 noise
            ];
        });

        // Sort by score descending
        $sorted = $candidates->sortByDesc('score');

        return $sorted->first()['agent'];
    }

    protected function calculateGoalPressure(Character $agent): float
    {
        $highest = $agent->getGoals()->getHighestPriority();
        if (!$highest) {
            return 0.0;
        }
        // Normalize priority (assuming max ~100)
        return min(1.0, $highest['priority'] / 100);
    }

    protected function calculateEmotionPressure(Character $agent): float
    {
        $maxIntensity = 0.0;
        foreach ($agent->getEmotions() as $emotion) {
            if ($emotion->intensity > $maxIntensity) {
                $maxIntensity = $emotion->intensity;
            }
        }
        return $maxIntensity;
    }
}
