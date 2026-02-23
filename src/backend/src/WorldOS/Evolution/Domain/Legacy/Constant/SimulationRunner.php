<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Constant;

use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector;
use WorldOS\Legacy\Domain\Cosmology\Repository\CosmologyRepository;
use WorldOS\Evolution\Domain\Legacy\Service\BasePhysicsEngine;

/**
 * Runs N ticks deterministic (optional seed for initial state). No LLM; no ChronicleEventWriter.
 * Uses ArcDetector + PresetTransitionEngine + RegimeModifier.
 */
final class SimulationRunner
{
    public function __construct(
        private readonly BasePhysicsEngine $kernel,
        private readonly ArcDetector $arcDetector,
        private readonly PresetTransitionEngine $presetEngine,
        private readonly ?CosmologyRepository $repository = null
    ) {
    }

    public function run(SimulationConfig $config): SimulationResult
    {
        $state = $this->resolveInitialState($config);
        $arcMemory = new ArcMemory(ArcPhase::EXPANSION, 0);

        $snapshots = [];
        $events = [];
        $metrics = ['tick' => [], 'entropy' => [], 'arc_phase' => []];

        for ($tick = 1; $tick <= $config->ticks; $tick++) {
            $detected = $this->arcDetector->detect($state);
            $effectivePhase = $arcMemory->considerTransition($detected);
            $prevPhase = $arcMemory->current;
            $arcMemory = $arcMemory->advance($effectivePhase);

            if ($effectivePhase !== $prevPhase) {
                $events[] = ['tick' => $tick, 'event' => 'arc_transition:' . $prevPhase->value . '->' . $effectivePhase->value];
            }

            $preset = $this->presetEngine->resolve($arcMemory->current);
            $regime = RegimeModifier::forPhase($arcMemory->current);
            $state = $this->kernel->evolve($state, $preset, $regime);

            $assessment = $this->kernel->getLastAssessment();
            if ($assessment !== null && !empty($assessment['should_collapse'])) {
                $events[] = ['tick' => $tick, 'event' => 'collapse'];
            }

            $metrics['tick'][] = $tick;
            $metrics['entropy'][] = $state->getEntropy();
            $metrics['arc_phase'][] = $arcMemory->current->value;

            if ($config->snapshotInterval > 0 && $tick % $config->snapshotInterval === 0) {
                $snapshots[] = [
                    'tick' => $tick,
                    'state' => $state->getAll(),
                    'arc_phase' => $arcMemory->current->value,
                ];
            }
        }

        return new SimulationResult($snapshots, $events, $metrics);
    }

    private function resolveInitialState(SimulationConfig $config): WorldStateVector
    {
        if ($config->initialState !== null) {
            return WorldStateVector::fromArray($config->initialState);
        }
        if ($config->universeId !== null && $this->repository !== null) {
            $universe = $this->repository->find($config->universeId);
            if ($universe !== null) {
                return $universe->getState();
            }
        }
        return WorldStateVector::create(0.9, 0.1, 0.8, 0.9, 0.5, 0.2, 0.0, 0.0, 0.5, 0.5);
    }
}


