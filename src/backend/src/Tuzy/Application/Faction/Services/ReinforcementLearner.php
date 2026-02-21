<?php

namespace Tuzy\Application\Faction\Services;

use App\Models\Faction;
use Tuzy\Domain\Faction\ValueObject\FactionMemory;
use Tuzy\Domain\Faction\ValueObject\PersonalityVector;
use Tuzy\Domain\Faction\ValueObject\IdeologyVector;

class ReinforcementLearner
{
    /**
     * Process outcome of an intent and update faction state.
     */
    public function processOutcome(Faction $faction, float $reward): void
    {
        $memory = $faction->getMemory();
        $personality = $faction->getPersonality();

        // 1. Update Memory
        $memory->successScore = ($memory->successScore * 0.9) + ($reward * 0.1);
        
        // Handle specific feedback types (in a real system, reward would be structured)
        if ($reward < -0.5) {
            $memory->warFatigue += 0.1;
        } else if ($reward > 0.5) {
            $memory->warFatigue = max(0.0, $memory->warFatigue - 0.05);
        }

        // 2. Personality Drift (Lightweight RL)
        // Adjust personality based on reward. 
        // Example: if aggressive action was rewarded, aggression increases.
        // This is a bit simplified here as we don't know the intent type from just $reward,
        // but typically the orchestrator passes structured feedback.
        
        $faction->updateMemory($memory);
        $faction->save();
    }

    /**
     * Structured outcome processing.
     */
    public function updatePersonality(Faction $faction, string $intentType, float $reward): void
    {
        $personality = $faction->getPersonality();
        
        // Personality drift logic
        $drift = 0.05 * $reward;
        
        $aggression = $personality->aggression;
        $faith = $personality->faith;
        $fear = $personality->fear;
        $ambition = $personality->ambition;
        $rationality = $personality->rationality;

        switch($intentType) {
            case 'attack':
            case 'expand':
                $aggression = max(0.0, min(1.0, $aggression + $drift));
                $ambition = max(0.0, min(1.0, $ambition + $drift));
                break;
            case 'alliance':
            case 'recover':
                $aggression = max(0.0, min(1.0, $aggression - $drift));
                $fear = max(0.0, min(1.0, $fear - $drift));
                break;
            case 'invoke_myth':
                $faith = max(0.0, min(1.0, $faith + $drift));
                break;
        }

        $newPersonality = new PersonalityVector(
            $aggression,
            $rationality,
            $faith,
            $fear,
            $ambition
        );

        $faction->updatePersonality($newPersonality);
        $faction->save();
    }

    public function updateIdeology(Faction $faction, string $intentType, float $reward): void
    {
        $ideology = $faction->getIdeology();
        
        // Ideology drifts slower than personality
        $drift = 0.02 * $reward;
        
        $militarism = $ideology->militarism;
        $spiritualism = $ideology->spiritualism;
        $expansionism = $ideology->expansionism;
        $collectivism = $ideology->collectivism;
        $purity = $ideology->purity;

        switch($intentType) {
            case 'attack':
                $militarism = $this->clamp($militarism + $drift);
                break;
            case 'alliance':
                $collectivism = $this->clamp($collectivism + $drift);
                break;
            case 'expand':
                $expansionism = $this->clamp($expansionism + $drift);
                break;
            case 'invoke_myth':
                $spiritualism = $this->clamp($spiritualism + $drift);
                break;
            case 'suppress_scar':
                $purity = $this->clamp($purity + $drift);
                break;
        }

        $newIdeology = new IdeologyVector(
            $militarism,
            $spiritualism,
            $expansionism,
            $collectivism,
            $purity
        );

        $faction->updateIdeology($newIdeology);
        $faction->save();
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
