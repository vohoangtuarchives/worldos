<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\UniverseStyleVersion;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;

/**
 * StyleAdvisorService — the Meta-AI advisor that proposes style changes.
 *
 * From RFC §7:
 *   - Analyzes current simulation trajectory
 *   - Proposes style weight adjustments (small δ)
 *   - Human approves/rejects proposals
 *   - Never modifies state directly
 *
 * The advisor is READ-ONLY with respect to simulation state.
 * It can only PROPOSE changes that humans must approve.
 */
class StyleAdvisorService
{
    private const MAX_DELTA = 0.15;     // Max per-weight adjustment
    private const PROPOSAL_COOLDOWN = 50; // Min ticks between proposals

    private int $lastProposalTick = -1000;

    public function __construct(
        private readonly QualityEvaluator $evaluator,
        private readonly SemanticProjector $projector,
    ) {}

    /**
     * Analyze trajectory and produce a style improvement proposal.
     *
     * @param WorldSnapshot[] $trajectory Recent simulation snapshots
     * @param UniverseStyleVersion $currentStyle Current active style
     * @param int $currentTick Current simulation tick
     * @return array{proposal: ?array, analysis: array}
     */
    public function analyze(array $trajectory, UniverseStyleVersion $currentStyle, int $currentTick): array
    {
        // Cooldown check
        if ($currentTick - $this->lastProposalTick < self::PROPOSAL_COOLDOWN) {
            return ['proposal' => null, 'analysis' => ['reason' => 'cooldown_active']];
        }

        // Evaluate quality
        $quality = $this->evaluator->evaluate($trajectory);
        $gi = $quality['grandness_index'];
        $metrics = $quality['metrics'];

        // Project current semantic state
        $lastSnap = end($trajectory);
        $semantic = $lastSnap ? $this->projector->projectState($lastSnap->cosmic) : [];
        $archetype = $lastSnap ? $this->projector->matchArchetype($semantic) : [];

        $analysis = [
            'grandness_index' => $gi,
            'current_archetype' => $archetype['archetype'] ?? 'unknown',
            'archetype_similarity' => $archetype['similarity'] ?? 0.0,
            'key_metrics' => $metrics,
        ];

        // Only propose if GI can be improved
        if ($gi > 0.8) {
            return ['proposal' => null, 'analysis' => array_merge($analysis, ['reason' => 'quality_sufficient'])];
        }

        // Generate proposal based on weakest metrics
        $proposal = $this->generateProposal($metrics, $currentStyle, $gi);
        $this->lastProposalTick = $currentTick;

        return ['proposal' => $proposal, 'analysis' => $analysis];
    }

    /**
     * Generate a specific style adjustment proposal.
     */
    private function generateProposal(array $metrics, UniverseStyleVersion $currentStyle, float $gi): array
    {
        $weightAdjustments = [];
        $reason = [];

        // Rule 1: Low order dominance → increase order_bias
        $orderRatio = $metrics['order_dominance_ratio'] ?? 0.5;
        if ($orderRatio < 0.4) {
            $delta = min(self::MAX_DELTA, (0.5 - $orderRatio) * 0.3);
            $weightAdjustments['order_bias'] = $delta;
            $reason[] = "Order dominance low ({$orderRatio}), suggest +{$delta}";
        }

        // Rule 2: High fragmentation → reduce chaos_sensitivity
        $fragmentation = $metrics['fragmentation_index'] ?? 0.0;
        if ($fragmentation > 0.5) {
            $delta = min(self::MAX_DELTA, ($fragmentation - 0.3) * 0.2);
            $weightAdjustments['chaos_sensitivity'] = -$delta;
            $reason[] = "High fragmentation ({$fragmentation}), suggest reducing chaos sensitivity";
        }

        // Rule 3: Low arc smoothness → increase emergence_threshold
        $smoothness = $metrics['arc_smoothness'] ?? 0.5;
        if ($smoothness < 0.4) {
            $delta = min(self::MAX_DELTA, (0.5 - $smoothness) * 0.2);
            $weightAdjustments['emergence_threshold'] = $delta;
            $reason[] = "Low smoothness ({$smoothness}), suggest raising emergence threshold";
        }

        // Rule 4: Low diversity → increase diversity_bias
        $diversity = $metrics['archetype_distribution_entropy'] ?? 0.5;
        if ($diversity < 0.3) {
            $delta = min(self::MAX_DELTA, (0.5 - $diversity) * 0.3);
            $weightAdjustments['diversity_bias'] = $delta;
            $reason[] = "Low archetype diversity ({$diversity}), suggest +{$delta}";
        }

        if (empty($weightAdjustments)) {
            $weightAdjustments['order_bias'] = 0.05; // Default small nudge
            $reason[] = "General quality improvement nudge";
        }

        return [
            'type' => 'STYLE_WEIGHT_ADJUSTMENT',
            'weight_adjustments' => $weightAdjustments,
            'predicted_gi_improvement' => round(array_sum(array_map('abs', $weightAdjustments)) * 0.1, 4),
            'reason' => $reason,
            'requires_human_approval' => true,
            'current_gi' => $gi,
        ];
    }
}
