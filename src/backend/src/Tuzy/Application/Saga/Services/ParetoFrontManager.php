<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\Services;

use Tuzy\Domain\Saga\SagaGeneration;
use Tuzy\Domain\Saga\SagaWorld;

/**
 * Phase 4.1: Lưu generation history (saga_generations); dominance; StabilityConstraint gating.
 * A dominates B iff A không tệ hơn mọi objective và tốt hơn ít nhất một.
 */
final class ParetoFrontManager
{
    public function __construct(
        private readonly StabilityConstraint $stabilityConstraint
    ) {
    }

    /**
     * Record one generation (world outcome). If stability violated, stability_flag = true (excluded from Pareto).
     */
    public function record(
        string $sagaId,
        string $worldId,
        int $sequence,
        array $objectiveVector,
        ?string $archetype = null
    ): SagaGeneration {
        $stabilityFlag = $this->stabilityConstraint->violated($objectiveVector);
        return SagaGeneration::create([
            'saga_id' => $sagaId,
            'world_id' => $worldId,
            'sequence' => $sequence,
            'objective_vector' => $objectiveVector,
            'archetype' => $archetype,
            'stability_flag' => $stabilityFlag,
        ]);
    }

    /**
     * Whether A dominates B (A not worse on any objective, better on at least one).
     * Uses keys present in both; higher is better for all dimensions.
     *
     * @param array<string, float> $a
     * @param array<string, float> $b
     */
    public function dominates(array $a, array $b): bool
    {
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        $notWorse = true;
        $strictlyBetter = false;
        foreach ($keys as $k) {
            $va = (float) ($a[$k] ?? 0);
            $vb = (float) ($b[$k] ?? 0);
            if ($va < $vb) {
                $notWorse = false;
                break;
            }
            if ($va > $vb) {
                $strictlyBetter = true;
            }
        }
        return $notWorse && $strictlyBetter;
    }

    /**
     * Current Pareto front for saga (non-dominated generations with stability_flag = false).
     *
     * @return list<SagaGeneration>
     */
    public function getCurrentParetoFront(string $sagaId): array
    {
        $generations = SagaGeneration::where('saga_id', $sagaId)
            ->where('stability_flag', false)
            ->orderBy('sequence')
            ->get();

        $front = [];
        foreach ($generations as $g) {
            $ov = $g->objective_vector ?? [];
            $dominated = false;
            foreach ($generations as $other) {
                if ($other->id === $g->id) {
                    continue;
                }
                $otherOv = $other->objective_vector ?? [];
                if ($this->dominates($otherOv, $ov)) {
                    $dominated = true;
                    break;
                }
            }
            if (!$dominated) {
                $front[] = $g;
            }
        }
        return $front;
    }
}
