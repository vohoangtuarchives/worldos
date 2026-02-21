<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\CosmicState;
use WorldOS\Domains\Evolution\ValueObjects\UniverseStyleVersion;
use WorldOS\Domains\Evolution\ValueObjects\WorldSnapshot;

/**
 * SimulationSandbox â€” runs what-if scenarios before applying style changes.
 *
 * From RFC Â§7.3:
 *   - Clone current world state
 *   - Apply proposed style change
 *   - Run N ticks in sandbox
 *   - Compare GI before/after
 *   - Report delta to human for decision
 *
 * The sandbox is ISOLATED â€” it never mutates the real simulation.
 */
class SimulationSandbox
{
    public function __construct(
        private readonly WorldEvolutionPipeline $pipeline,
        private readonly QualityEvaluator $evaluator,
    ) {}

    /**
     * Run a sandbox simulation with a proposed style change.
     *
     * @param WorldSnapshot $currentSnapshot Current world state (will be cloned)
     * @param UniverseStyleVersion $proposedStyle The proposed style
     * @param int $ticks Number of ticks to simulate
     * @param int $deltaYears Years per tick
     * @return array{baseline_gi: float, proposed_gi: float, delta_gi: float, trajectory_length: int, safe: bool}
     */
    public function runScenario(
        WorldSnapshot $currentSnapshot,
        UniverseStyleVersion $proposedStyle,
        int $ticks = 20,
        int $deltaYears = 100,
    ): array {
        // Run baseline (no style change)
        $baselineTrajectory = $this->simulateTrajectory($currentSnapshot, $ticks, $deltaYears);
        $baselineResult = $this->evaluator->evaluate($baselineTrajectory);
        $baselineGI = $baselineResult['grandness_index'];

        // Run proposed scenario (with style bias applied via snapshot manipulation)
        $biasedSnapshot = $this->applyStyleBias($currentSnapshot, $proposedStyle);
        $proposedTrajectory = $this->simulateTrajectory($biasedSnapshot, $ticks, $deltaYears);
        $proposedResult = $this->evaluator->evaluate($proposedTrajectory);
        $proposedGI = $proposedResult['grandness_index'];

        $deltaGI = $proposedGI - $baselineGI;

        // Safety check: reject if proposed scenario leads to void collapse
        $safe = $this->isSafe($proposedTrajectory);

        return [
            'baseline_gi' => $baselineGI,
            'proposed_gi' => $proposedGI,
            'delta_gi' => round($deltaGI, 4),
            'trajectory_length' => count($proposedTrajectory),
            'safe' => $safe,
        ];
    }

    /**
     * Run a simple trajectory simulation.
     *
     * @return WorldSnapshot[]
     */
    private function simulateTrajectory(WorldSnapshot $start, int $ticks, int $deltaYears): array
    {
        $trajectory = [$start];
        $snapshot = $start;

        for ($i = 0; $i < $ticks; $i++) {
            $result = $this->pipeline->step($snapshot, $deltaYears);
            $snapshot = $result['snapshot'];
            $trajectory[] = $snapshot;
        }

        return $trajectory;
    }

    /**
     * Apply style bias to a snapshot (adjusts cosmic state slightly).
     */
    private function applyStyleBias(WorldSnapshot $snapshot, UniverseStyleVersion $style): WorldSnapshot
    {
        $bias = $style->styleBias($snapshot->cosmic->currentAttractor);
        $cosmic = $snapshot->cosmic;

        $newCosmic = new CosmicState(
            entropy: $this->clamp($cosmic->entropy + ($bias['entropy'] ?? 0.0)),
            energy: $this->clamp($cosmic->energy + ($bias['energy'] ?? 0.0)),
            causality: $cosmic->causality,
            strain: $this->clamp($cosmic->strain + ($bias['strain'] ?? 0.0)),
            stability: $this->clamp($cosmic->stability + ($bias['stability'] ?? 0.0)),
            currentAttractor: $cosmic->currentAttractor,
            year: $cosmic->year,
        );

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            worldField: $snapshot->worldField,
            worldPhase: $snapshot->worldPhase,
            lifeState: $snapshot->lifeState,
            year: $snapshot->year,
        );
    }

    /**
     * Safety check: trajectory should not end in permanent void collapse.
     */
    private function isSafe(array $trajectory): bool
    {
        // Check last 5 snapshots â€” if all have very low stability/energy, it's unsafe
        $last5 = array_slice($trajectory, -5);
        $allCollapsed = true;

        foreach ($last5 as $snap) {
            if ($snap->cosmic->stability > 0.2 || $snap->cosmic->energy > 0.2) {
                $allCollapsed = false;
                break;
            }
        }

        return !$allCollapsed;
    }

    private function clamp(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }
}



