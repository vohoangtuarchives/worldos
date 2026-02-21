<?php

namespace Tuzy\Application\Faction\Services;

use App\Models\Faction;
use App\Models\World;
use App\Models\FactionHistoryLog;
use Tuzy\Domain\Faction\Enums\FactionIntentType;

class FactionAgent
{
    public function __construct(
        private DecisionEngine $decisionEngine,
        private ReinforcementLearner $learner,
        private SuccessionService $succession,
        private \Tuzy\Application\History\Services\ScarImpactService $scarService,
        private EncounterService $encounterService
    ) {}

    /**
     * Run the agent's turn.
     */
    /**
     * Run the agent's turn.
     */
    public function executeTurn(
        Faction $faction, 
        World $world, 
        int $turn,
        ?\Tuzy\Domain\Cosmology\ValueObject\CosmicState $cosmic = null,
        ?\Tuzy\Domain\Cosmology\ValueObject\CivilizationState $civ = null
    ): void {
        // 0. Apply Historical Drift (Scars)
        $drift = $this->scarService->calculateFactionIdeologyDrift($faction, $turn);
        $this->applyIdeologyDrift($faction, $drift);

        // 1. Check for succession (aging/death)
        $this->succession->handleSuccession($faction);

        // 1.5 Check for Personal Encounters (Destiny)
        $this->encounterService->checkEncounter($faction);

        // 2. Decide next intent
        $intentScores = $this->decisionEngine->scoreIntents($faction, $world, $cosmic, $civ);
        $bestIntentValue = array_key_first($intentScores);
        $bestIntent = FactionIntentType::from($bestIntentValue);

        // 3. Log the decision
        $this->logDecision($faction, $turn, $bestIntent, $intentScores);

        // 4. In a real simulation, we would return the intent to a ConflictResolver.
        // For now, we store the current intent in attributes or a temporary state if needed.
        $attributes = $faction->attributes ?? [];
        $attributes['current_intent'] = $bestIntent->value;
        $faction->attributes = $attributes;
        $faction->save();
    }

    private function applyIdeologyDrift(Faction $faction, array $drift): void
    {
        $ideology = $faction->getIdeology();
        
        $newIdeology = new \Tuzy\Domain\Faction\ValueObject\IdeologyVector(
            militarism: $this->clamp($ideology->militarism + ($drift['militarism'] ?? 0)),
            spiritualism: $this->clamp($ideology->spiritualism + ($drift['spiritualism'] ?? 0)),
            expansionism: $this->clamp($ideology->expansionism + ($drift['expansionism'] ?? 0)),
            collectivism: $this->clamp($ideology->collectivism + ($drift['collectivism'] ?? 0)),
            purity: $this->clamp($ideology->purity + ($drift['purity'] ?? 0))
        );

        $faction->updateIdeology($newIdeology);
        $faction->save(); // Save intermediate state or rely on final save? Better safe.
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    /**
     * Process the outcome of a chosen intent.
     */
    public function recordOutcome(Faction $faction, int $turn, float $reward, array $reasoning = []): void
    {
        $currentIntent = $faction->attributes['current_intent'] ?? 'recover';

        // 1. Update Learning
        $this->learner->updatePersonality($faction, $currentIntent, $reward);
        $this->learner->processOutcome($faction, $reward);

        // 2. Update Log
        FactionHistoryLog::where('faction_id', $faction->id)
            ->where('turn', $turn)
            ->update([
                'outcome_score' => $reward,
                'reasoning' => array_merge($reasoning, ['final_reward' => $reward])
            ]);
    }

    private function logDecision(Faction $faction, int $turn, FactionIntentType $intent, array $scores): void
    {
        FactionHistoryLog::create([
            'faction_id' => $faction->id,
            'turn' => $turn,
            'intent_type' => $intent->value,
            'reasoning' => [
                'scores' => $scores,
                'leader' => $faction->getLeader()->name,
                'generation' => $faction->current_generation
            ]
        ]);
    }
}
