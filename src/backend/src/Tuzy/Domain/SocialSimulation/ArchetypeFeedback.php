<?php

namespace Tuzy\Domain\SocialSimulation;

use Tuzy\Application\CognitiveKernel\Drift\DriftApplier;
use Tuzy\Domain\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Archetype Feedback Loop
 * 
 * Creates feedback from economy/power actions → archetype drift
 * 
 * Key Insight:
 * Actions don't just get filtered by archetypes.
 * Over time, actions also SHAPE archetypes.
 * 
 * Example:
 * - Repeated economic extraction → "sacrifice" archetype drifts
 * - Unchallenged power → "submission" increases, "rebellion" decreases
 */
class ArchetypeFeedback
{
    private DriftApplier $driftApplier;

    public function __construct()
    {
        $this->driftApplier = new DriftApplier();
    }

    /**
     * Apply feedback from economic action to archetypes
     */
    public function applyEconomicFeedback(
        World $world,
        array $economicAction,
        array $filteredPerception
    ): void {
        $actionType = $economicAction['type'] ?? 'unknown';
        $severity = $economicAction['severity'] ?? 0.5;

        // Different action types strengthen/weaken different archetypes
        $feedbackRules = $this->getEconomicFeedbackRules($actionType, $severity);

        $this->applyFeedbackToArchetypes($world, $feedbackRules);
    }

    /**
     * Apply feedback from power action to archetypes
     */
    public function applyPowerFeedback(
        World $world,
        array $powerAction,
        array $filteredPerception
    ): void {
        $actionType = $powerAction['type'] ?? 'unknown';
        $coercion = $powerAction['coercion'] ?? 0.5;

        $feedbackRules = $this->getPowerFeedbackRules($actionType, $coercion);

        $this->applyFeedbackToArchetypes($world, $feedbackRules);
    }

    /**
     * Get feedback rules for economic actions
     */
    private function getEconomicFeedbackRules(string $actionType, float $severity): array
    {
        return match($actionType) {
            'scarcity' => [
                'sacrifice' => 0.02 * $severity,
                'equality' => -0.01 * $severity,
                'rebellion' => 0.01 * $severity,
            ],
            'inequality' => [
                'hierarchy' => 0.02 * $severity,
                'equality' => -0.03 * $severity,
                'rebellion' => 0.02 * $severity,
            ],
            'extraction' => [
                'submission' => 0.01 * $severity,
                'domination' => 0.02 * $severity,
                'rebellion' => -0.01 * $severity,
            ],
            default => [],
        };
    }

    /**
     * Get feedback rules for power actions
     */
    private function getPowerFeedbackRules(string $actionType, float $coercion): array
    {
        return match($actionType) {
            'coercion' => [
                'domination' => 0.02 * $coercion,
                'submission' => 0.01 * $coercion,
                'rebellion' => -0.02 * $coercion,
            ],
            'oppression' => [
                'silence' => 0.03 * $coercion,
                'fear' => 0.02 * $coercion,
                'rebellion' => -0.01 * $coercion,
            ],
            'liberation' => [
                'rebellion' => 0.03 * (1 - $coercion),
                'freedom' => 0.02 * (1 - $coercion),
                'submission' => -0.02 * (1 - $coercion),
            ],
            default => [],
        };
    }

    /**
     * Apply feedback rules to archetype weights
     */
    private function applyFeedbackToArchetypes(World $world, array $feedbackRules): void
    {
        foreach ($feedbackRules as $archetypeKey => $delta) {
            $weight = ArchetypeWeight::where('world_id', $world->id)
                ->where('archetype_key', $archetypeKey)
                ->first();

            if (!$weight) {
                continue;
            }

            // Record as special drift source: "feedback"
            $weight->recordDrift($delta, [
                'feedback' => abs($delta),
                'source' => 'social_action_feedback'
            ]);
        }
    }
}
