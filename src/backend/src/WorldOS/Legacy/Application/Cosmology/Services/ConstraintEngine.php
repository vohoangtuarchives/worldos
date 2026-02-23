<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Services;

/**
 * ConstraintEngine — enforces hard limits on Meta-AI proposals.
 *
 * From RFC §7.5:
 *   Safety Guardrails:
 *   1. Max memory bias: ||MemoryBias|| ≤ 0.25
 *   2. Max style weight delta: |δ| ≤ 0.15 per adjustment
 *   3. Archetype redundancy check (cosine similarity ≥ 0.92 → reject)
 *   4. Min stability margin before applying style changes
 *   5. Max proposal frequency (cooldown)
 *
 * This engine VETOES unsafe proposals. It cannot approve — only block.
 */
class ConstraintEngine
{
    private const MAX_WEIGHT_DELTA = 0.15;
    private const MAX_MEMORY_BIAS_MAGNITUDE = 0.25;
    private const MIN_STABILITY_FOR_CHANGE = 0.3;
    private const ARCHETYPE_REDUNDANCY_THRESHOLD = 0.92;

    /**
     * Validate a style adjustment proposal.
     *
     * @param array $proposal The proposal from StyleAdvisorService
     * @param float $currentStability Current world stability
     * @return array{valid: bool, violations: array}
     */
    public function validateProposal(array $proposal, float $currentStability): array
    {
        $violations = [];

        // Check weight deltas
        $adjustments = $proposal['weight_adjustments'] ?? [];
        foreach ($adjustments as $key => $delta) {
            if (abs($delta) > self::MAX_WEIGHT_DELTA) {
                $violations[] = "Weight delta for '{$key}' exceeds max: |{$delta}| > " . self::MAX_WEIGHT_DELTA;
            }
        }

        // Check stability margin
        if ($currentStability < self::MIN_STABILITY_FOR_CHANGE) {
            $violations[] = "Stability too low for changes: {$currentStability} < " . self::MIN_STABILITY_FOR_CHANGE;
        }

        // Require human approval flag
        if (empty($proposal['requires_human_approval'])) {
            $violations[] = "Proposal must have requires_human_approval = true";
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
        ];
    }

    /**
     * Validate a memory bias vector.
     *
     * @param array $bias Memory bias vector
     * @return array{valid: bool, magnitude: float, violation: ?string}
     */
    public function validateMemoryBias(array $bias): array
    {
        $magnitude = 0.0;
        foreach ($bias as $v) {
            $magnitude += $v * $v;
        }
        $magnitude = sqrt($magnitude);

        $valid = $magnitude <= self::MAX_MEMORY_BIAS_MAGNITUDE + 0.001; // Small epsilon

        return [
            'valid' => $valid,
            'magnitude' => round($magnitude, 4),
            'violation' => $valid ? null : "Memory bias magnitude {$magnitude} exceeds max " . self::MAX_MEMORY_BIAS_MAGNITUDE,
        ];
    }

    /**
     * Validate an emergent archetype proposal.
     *
     * @param array $proposedSemantic Proposed semantic vector
     * @param SemanticProjector $projector For redundancy checking
     * @return array{valid: bool, violation: ?string}
     */
    public function validateEmergentArchetype(array $proposedSemantic, SemanticProjector $projector): array
    {
        if ($projector->isRedundant($proposedSemantic, self::ARCHETYPE_REDUNDANCY_THRESHOLD)) {
            return [
                'valid' => false,
                'violation' => 'Proposed archetype too similar to existing (cosine ≥ ' . self::ARCHETYPE_REDUNDANCY_THRESHOLD . ')',
            ];
        }

        return ['valid' => true, 'violation' => null];
    }

    /**
     * Validate a sandbox result before allowing style transition.
     *
     * @param array $sandboxResult Result from SimulationSandbox
     * @return array{valid: bool, violations: array}
     */
    public function validateSandboxResult(array $sandboxResult): array
    {
        $violations = [];

        if (!($sandboxResult['safe'] ?? true)) {
            $violations[] = "Sandbox scenario is unsafe (potential void collapse)";
        }

        if (($sandboxResult['delta_gi'] ?? 0) < -0.1) {
            $violations[] = "Proposed change significantly worsens GI: delta = {$sandboxResult['delta_gi']}";
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
        ];
    }
}
