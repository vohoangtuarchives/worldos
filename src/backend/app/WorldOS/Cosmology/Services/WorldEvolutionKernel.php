<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\Services;

use App\WorldOS\Cosmology\Contracts\CascadeEngineInterface;
use App\WorldOS\Cosmology\Contracts\PhysicsEngineInterface;
use App\WorldOS\Cosmology\Contracts\StabilityAnalyzerInterface;
use App\WorldOS\Cosmology\ValueObjects\CascadeThresholds;
use App\WorldOS\Cosmology\ValueObjects\EvolutionResult;
use App\WorldOS\Cosmology\ValueObjects\PhaseTransition;
use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\StabilityMetric;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use App\WorldOS\World\Entities\WorldEntity;

/**
 * World Evolution Kernel — Single Authority for tick execution.
 *
 * Orchestrator that composes computation engines (via interfaces) into a single tick.
 * Does NOT contain any math — delegates to PhysicsEngine, CascadeEngine, StabilityAnalyzer.
 *
 * Dependencies are injected via Cosmology Contracts (interfaces).
 * SimulationEngine provides the actual implementations.
 */
final class WorldEvolutionKernel
{
    public function __construct(
        private readonly PhysicsEngineInterface $physicsEngine,
        private readonly CascadeEngineInterface $cascadeEngine,
        private readonly StabilityAnalyzerInterface $stabilityAnalyzer,
    ) {
    }

    /**
     * Execute one tick of universe evolution.
     */
    public function tick(
        WorldEntity $world,
        UniverseEntity $universe,
        ?CascadeThresholds $thresholds = null,
    ): EvolutionResult {
        $law = $world->getLawVector();
        $currentState = $universe->getStateVector();
        $currentCascade = $universe->getCascadeState();
        $currentTick = $universe->getCurrentTick();
        $thresholds = $thresholds ?? CascadeThresholds::defaults();

        // 1. Evolve WorldStateVector (macro-state) — delegated to PhysicsEngine
        $newState = $this->physicsEngine->evolve($currentState, $law);

        // 2. Evolve CascadeStateVector (layer emergence) — delegated to CascadeEngine
        $newCascade = $this->cascadeEngine->evolve($currentCascade, $law, $thresholds);

        // 3. Analyze stability — delegated to StabilityAnalyzer
        $stability = $this->stabilityAnalyzer->analyze($newState, $newCascade);

        // 4. Detect phase transitions (orchestration logic — belongs here)
        $phaseTransitions = $this->detectPhaseTransitions(
            $currentState,
            $newState,
            $currentCascade,
            $newCascade,
            $stability,
            $currentTick + 1,
        );

        // 5. Check for collapse
        $collapseDetected = $stability->isCollapsing();
        $collapseReason = $collapseDetected
            ? $this->determineCollapseReason($newState, $newCascade)
            : null;

        // 6. Build metrics
        $metrics = [
            'stability' => $stability->value,
            'entropy_delta' => $newState->entropy - $currentState->entropy,
            'cascade_highest_layer' => $newCascade->highestActiveLayer(),
            'phase_transitions_count' => count($phaseTransitions),
            'collapse_detected' => $collapseDetected,
        ];

        return new EvolutionResult(
            newStateVector: $newState,
            newCascadeState: $newCascade,
            stability: $stability,
            phaseTransitions: $phaseTransitions,
            collapseDetected: $collapseDetected,
            collapseReason: $collapseReason,
            metrics: $metrics,
        );
    }

    /**
     * @return PhaseTransition[]
     */
    private function detectPhaseTransitions(
        WorldStateVector $oldState,
        WorldStateVector $newState,
        CascadeStateVector $oldCascade,
        CascadeStateVector $newCascade,
        StabilityMetric $stability,
        int $tick,
    ): array {
        $transitions = [];

        // Cascade layer activation/collapse detection
        $layerNames = ['physics', 'chemistry', 'biology', 'cognition', 'culture'];
        $oldLayers = $oldCascade->toArray();
        $newLayers = $newCascade->toArray();
        $activationThreshold = 0.1;

        foreach ($layerNames as $name) {
            $oldVal = $oldLayers[$name];
            $newVal = $newLayers[$name];

            if ($oldVal < $activationThreshold && $newVal >= $activationThreshold) {
                $transitions[] = new PhaseTransition(
                    type: 'cascade_activation',
                    from: 'inactive',
                    to: $name,
                    tick: $tick,
                    magnitude: $newVal,
                    description: "Layer '{$name}' has emerged (value: {$newVal})",
                );
            }

            if ($oldVal >= $activationThreshold && $newVal < $activationThreshold) {
                $transitions[] = new PhaseTransition(
                    type: 'cascade_collapse',
                    from: $name,
                    to: 'inactive',
                    tick: $tick,
                    magnitude: 1.0 - $newVal,
                    description: "Layer '{$name}' has collapsed (value: {$newVal})",
                );
            }
        }

        // Stability regime change
        $oldStability = $this->stabilityAnalyzer->analyze($oldState, $oldCascade);
        $oldRegime = $this->getRegimeLabel($oldStability);
        $newRegime = $this->getRegimeLabel($stability);

        if ($oldRegime !== $newRegime) {
            $transitions[] = new PhaseTransition(
                type: 'stability_regime_change',
                from: $oldRegime,
                to: $newRegime,
                tick: $tick,
                magnitude: abs($stability->value - $oldStability->value),
                description: "Stability regime changed: {$oldRegime} → {$newRegime}",
            );
        }

        return $transitions;
    }

    private function getRegimeLabel(StabilityMetric $stability): string
    {
        if ($stability->isCollapsing()) {
            return 'collapse';
        }
        if ($stability->isCrisis()) {
            return 'crisis';
        }
        if ($stability->isNormal()) {
            return 'normal';
        }

        return 'stable';
    }

    private function determineCollapseReason(
        WorldStateVector $state,
        CascadeStateVector $cascade,
    ): string {
        $reasons = [];

        if ($state->entropy > 0.85) {
            $reasons[] = 'entropy_death';
        }
        if ($state->trauma > 0.8) {
            $reasons[] = 'trauma_overload';
        }
        if ($cascade->physics < 0.1) {
            $reasons[] = 'physics_collapse';
        }
        if ($state->cohesion < 0.05 && $state->order < 0.05) {
            $reasons[] = 'total_disorder';
        }

        return implode('+', $reasons) ?: 'unknown';
    }
}
