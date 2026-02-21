<?php

namespace App\Domains\Faction\Services;

use App\Models\Faction;
use App\Models\World;
use Tuzy\Domain\Faction\Enums\FactionIntentType;

class DecisionEngine
{
    /**
     * Score all possible intents and return the best one.
     */
    /**
     * Score all possible intents and return the best one.
     */
    public function scoreIntents(
        Faction $faction, 
        World $world,
        ?\Tuzy\Domain\Cosmology\ValueObject\CosmicState $cosmic = null,
        ?\Tuzy\Domain\Cosmology\ValueObject\CivilizationState $civ = null
    ): array {
        $personality = $faction->getPersonality();
        $ideology = $faction->getIdeology();
        $memory = $faction->getMemory();

        $scores = [];

        foreach (FactionIntentType::cases() as $intent) {
            $scores[$intent->value] = $this->calculateUtility($intent, $faction, $world, $personality, $ideology, $memory, $cosmic, $civ);
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
        $memory,
        $cosmic,
        $civ
    ): float {
        return match ($intent) {
            FactionIntentType::SURVIVE => 0.5,
            FactionIntentType::ATTACK => $this->scoreAttack($faction, $world, $personality, $ideology, $memory, $cosmic),
            FactionIntentType::EXPAND => $this->scoreExpand($faction, $world, $personality, $ideology, $memory, $cosmic),
            FactionIntentType::ALLIANCE => $this->scoreAlliance($faction, $world, $personality, $ideology, $memory, $cosmic),
            FactionIntentType::INVOKE_MYTH => $this->scoreInvokeMyth($faction, $world, $personality, $ideology, $memory, $civ),
            FactionIntentType::SUPPRESS_SCAR => $this->scoreSuppressScar($faction, $world, $personality, $ideology, $memory),
            FactionIntentType::SPLIT => $this->scoreSplit($faction, $world, $personality, $ideology, $memory, $cosmic),
            FactionIntentType::RECOVER => $this->scoreRecover($faction, $world, $personality, $ideology, $memory, $civ),
            FactionIntentType::STABILIZE => $this->scoreStabilize($faction, $world, $personality, $ideology, $memory, $cosmic),
            FactionIntentType::PURGE => $this->scorePurge($faction, $world, $personality, $ideology, $memory, $cosmic, $civ),
            FactionIntentType::REFORM => $this->scoreReform($faction, $world, $personality, $ideology, $memory, $cosmic, $civ),
            FactionIntentType::ADAPT => $this->scoreAdapt($faction, $world, $personality, $ideology, $memory, $cosmic, $civ),
        };
    }

    private function scoreAttack(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic): float
    {
        // Base on Aggression and Militarism
        $score = ($personality->aggression * 0.6) + ($ideology->militarism * 0.4);
        
        // Boost if ambitious and powerful
        $score += ($personality->ambition * 0.3);
        
        // Strain increases aggression (Resource Wars)
        if ($cosmic && $cosmic->strain > 0.6) {
            $score += 0.3;
        }

        // Penalty if high fear
        $score -= ($personality->fear * 0.4);

        // Penalty if war fatigue is high
        $score -= ($memory->warFatigue * 0.5);

        return $score;
    }

    private function scoreExpand(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic): float
    {
        // Base on Expansionism and Ambition
        $score = ($ideology->expansionism * 0.7) + ($personality->ambition * 0.3);
        
        // Penalty if isolationist/purity is high
        $score -= ($ideology->purity * 0.2);

        // Entropy inhibits expansion
        if ($cosmic && $cosmic->entropy > 0.7) {
            $score -= 0.3;
        }

        return $score;
    }

    private function scoreAlliance(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic): float
    {
        // Inverse of Aggression and Purity
        $score = (1.0 - $personality->aggression) * 0.4;
        $score += (1.0 - $ideology->purity) * 0.4;
        
        // Boost if fear is high (seek protection)
        $score += ($personality->fear * 0.5);

        // High entropy drives alliances for survival
        if ($cosmic && $cosmic->entropy > 0.8) {
            $score += 0.4;
        }

        return $score;
    }

    private function scoreInvokeMyth(Faction $faction, World $world, $personality, $ideology, $memory, $civ): float
    {
        // Base on Faith and Spiritualism
        $score = ($personality->faith * 0.6) + ($ideology->spiritualism * 0.4);
        
        // Penalty if previous myths caused backlash
        $score -= ($memory->mythBacklash * 0.3);

        // Boost if Ritual Coherence is low (need to restore faith)
        if ($civ && $civ->ritualCoherence < 0.3) {
            $score += 0.4;
        }

        return $score;
    }

    private function scoreSuppressScar(Faction $faction, World $world, $personality, $ideology, $memory): float
    {
        // Base on Rationality and Fear (prevention)
        $score = ($personality->rationality * 0.5) + ($personality->fear * 0.4);
        
        // Urgent if scars are high (logic elsewhere would check world state)
        return $score;
    }

    private function scoreSplit(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic): float
    {
        // Usually low, triggered by internal cohesion (handled in policy)
        // But some isolationist/pure personalities might prefer splitting
        $score = ($ideology->purity * 0.2) - 0.5;

        // High Strain promotes schisms
        if ($cosmic && $cosmic->strain > 0.8) {
            $score += 0.5;
        }

        return $score; 
    }

    private function scoreRecover(Faction $faction, World $world, $personality, $ideology, $memory, $civ): float
    {
        // Boost if war fatigue is high or fear is high
        $score = ($memory->warFatigue * 0.6) + ($personality->fear * 0.3);

        // Low Resilience forces recovery
        if ($civ && $civ->resilience < 0.3) {
            $score += 0.5;
        }

        return $score;
    }

    private function scoreStabilize(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic): float
    {
        // High rationality and high fear of collapse drive stabilization
        $score = ($personality->rationality * 0.5) + ($personality->fear * 0.4);
        
        // Boost if supportive/spiritual ideology (preserving order)
        $score += ($ideology->spiritualism * 0.3);

        // High Entropy demands stabilization
        if ($cosmic && $cosmic->entropy > 0.6) {
            $score += 0.4;
        }

        return $score;
    }

    // --- NEW INTENTS ---

    private function scorePurge(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic, $civ): float
    {
        // Requires high Purity and Aggression
        $score = ($ideology->purity * 0.6) + ($personality->aggression * 0.4);

        // Religious Schism Scenario: High Strain + Low Ritual
        if ($cosmic && $civ && $civ->ritualCoherence < 0.3 && $cosmic->strain > 0.5) {
            // Authoritarians purge, Liberals don't
            if ($ideology->collectivism > 0.6) {
                $score += 0.6;
            }
        }

        return $score - 0.2; // Default penalty (Purge is extreme)
    }

    private function scoreReform(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic, $civ): float
    {
        // Requires Rationality and Knowledge (Civ state)
        $score = ($personality->rationality * 0.5);

        // Cultural Renaissance / Scientific Breakthrough
        if ($civ && $civ->collectiveKnowledge > 1.2) {
            $score += 0.4;
        }

        // Needs high Resilience to afford reform
        if ($civ && $civ->resilience > 0.6) {
            $score += 0.2;
        }

        return $score;
    }

    private function scoreAdapt(Faction $faction, World $world, $personality, $ideology, $memory, $cosmic, $civ): float
    {
        // Pragmatism (Low Purity + High Rationality)
        $score = ((1.0 - $ideology->purity) * 0.4) + ($personality->rationality * 0.4);

        // High Entropy drives adaptation
        if ($cosmic && $cosmic->entropy > 0.7) {
            $score += 0.5;
        }

        return $score;
    }
}
