<?php

namespace App\Domains\Faction\Services;

use App\Models\Faction;
use App\Models\World;
use App\Domains\Faction\Enums\FactionIntentType;

class DecisionEngine
{
    /**
     * Score all possible intents and return the best one.
     */
    public function scoreIntents(Faction $faction, World $world): array
    {
        $personality = $faction->getPersonality();
        $ideology = $faction->getIdeology();
        $memory = $faction->getMemory();

        $scores = [];

        foreach (FactionIntentType::cases() as $intent) {
            $scores[$intent->value] = $this->calculateUtility($intent, $faction, $world, $personality, $ideology, $memory);
        }

        // Add some noise (Irrationality/Impulsiveness)
        // rationality = 1.0 means no noise, 0.0 means high noise
        $noiseFactor = 1.0 - $personality->rationality;
        foreach ($scores as $key => $score) {
            $scores[$key] += (mt_rand(-100, 100) / 100) * $noiseFactor * 0.5;
        }

        arsort($scores);

        return $scores;
    }

    private function calculateUtility(
        FactionIntentType $intent, 
        Faction $faction, 
        World $world,
        $personality, 
        $ideology, 
        $memory
    ): float {
        return match ($intent) {
            FactionIntentType::ATTACK => $this->scoreAttack($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::EXPAND => $this->scoreExpand($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::ALLIANCE => $this->scoreAlliance($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::INVOKE_MYTH => $this->scoreInvokeMyth($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::SUPPRESS_SCAR => $this->scoreSuppressScar($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::SPLIT => $this->scoreSplit($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::RECOVER => $this->scoreRecover($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::STABILIZE => $this->scoreStabilize($faction, $world, $personality, $ideology, $memory),
            default => 0.0,
        };
    }

    private function scoreAttack(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Base on Aggression and Militarism
        $score = ($personality->aggression * 0.6) + ($ideology->militarism * 0.4);
        
        // Boost if ambitious and powerful
        $score += ($personality->ambition * 0.3);
        
        // Penalty if high fear
        $score -= ($personality->fear * 0.4);

        // Penalty if war fatigue is high
        $score -= ($memory->warFatigue * 0.5);

        return $score;
    }

    private function scoreExpand(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Base on Expansionism and Ambition
        $score = ($ideology->expansionism * 0.7) + ($personality->ambition * 0.3);
        
        // Penalty if isolationist/purity is high
        $score -= ($ideology->purity * 0.2);

        return $score;
    }

    private function scoreAlliance(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Inverse of Aggression and Purity
        $score = (1.0 - $personality->aggression) * 0.4;
        $score += (1.0 - $ideology->purity) * 0.4;
        
        // Boost if fear is high (seek protection)
        $score += ($personality->fear * 0.5);

        return $score;
    }

    private function scoreInvokeMyth(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Base on Faith and Spiritualism
        $score = ($personality->faith * 0.6) + ($ideology->spiritualism * 0.4);
        
        // Penalty if previous myths caused backlash
        $score -= ($memory->mythBacklash * 0.3);

        return $score;
    }

    private function scoreSuppressScar(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Base on Rationality and Fear (prevention)
        $score = ($personality->rationality * 0.5) + ($personality->fear * 0.4);
        
        // Urgent if scars are high (logic elsewhere would check world state)
        return $score;
    }

    private function scoreSplit(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Usually low, triggered by internal cohesion (handled in policy)
        // But some isolationist/pure personalities might prefer splitting
        return ($ideology->purity * 0.2) - 0.5; 
    }

    private function scoreRecover(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Boost if war fatigue is high or fear is high
        return ($memory->warFatigue * 0.6) + ($personality->fear * 0.3);
    }

    private function scoreStabilize(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // High rationality and high fear of collapse drive stabilization
        $score = ($personality->rationality * 0.5) + ($personality->fear * 0.4);
        
        // Boost if supportive/spiritual ideology (preserving order)
        $score += ($ideology->spiritualism * 0.3);

        return $score;
    }
}
