<?php

namespace App\Domains\Material\Engine;

use App\Domains\Material\MaterialInstance;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\State\WorldState;
use App\Domains\Material\State\WorldStateRepository;
use App\Domains\Material\State\WorldStateMutator;
use Illuminate\Support\Collection;

/**
 * MaterialLawEngine - Core Historical Physics Engine
 * 
 * This is NOT a rule engine. This is the physics of history.
 * 
 * Tick Order (NON-NEGOTIABLE):
 * 1. Evaluate World State
 * 2. Resolve Activation
 * 3. Check Compatibility
 * 4. Apply Effects
 * 5. Process Decay
 * 6. Emit Legacy
 * 7. Feed Back into Memory
 */
class MaterialLawEngine
{
    private StateEvaluator $stateEvaluator;
    private ActivationResolver $activationResolver;
    private CompatibilityGate $compatibilityGate;
    private EffectApplier $effectApplier;
    private DecayProcessor $decayProcessor;
    private LegacyEmitter $legacyEmitter;
    private MemoryFeedbackLoop $memoryFeedbackLoop;
    private MaterialRepositoryInterface $repository;
    private WorldStateRepository $stateRepository;
    private WorldStateMutator $stateMutator;

    public function __construct(
        StateEvaluator $stateEvaluator,
        ActivationResolver $activationResolver,
        CompatibilityGate $compatibilityGate,
        EffectApplier $effectApplier,
        DecayProcessor $decayProcessor,
        LegacyEmitter $legacyEmitter,
        MemoryFeedbackLoop $memoryFeedbackLoop,
        MaterialRepositoryInterface $repository,
        WorldStateRepository $stateRepository,
        WorldStateMutator $stateMutator
    ) {
        $this->stateEvaluator = $stateEvaluator;
        $this->activationResolver = $activationResolver;
        $this->compatibilityGate = $compatibilityGate;
        $this->effectApplier = $effectApplier;
        $this->decayProcessor = $decayProcessor;
        $this->legacyEmitter = $legacyEmitter;
        $this->memoryFeedbackLoop = $memoryFeedbackLoop;
        $this->repository = $repository;
        $this->stateRepository = $stateRepository;
        $this->stateMutator = $stateMutator;
    }

    /**
     * Process a single tick for all materials in a world.
     * This is the core physics loop.
     * 
     * @param int $worldId
     * @param int $epoch Current epoch/tick number
     * @param float $deltaTime Time passed in this tick (in years)
     * @return array Tick results
     */
    public function processTick(int $worldId, int $epoch, float $deltaTime): array
    {
        $instances = $this->repository->getInstancesForWorld($worldId);
        
        $tickResults = [
            'epoch' => $epoch,
            'world_id' => $worldId,
            'timestamp' => now()->toIso8601String(),
            'delta_time' => $deltaTime,
            'state_evaluation' => [],
            'activations' => [],
            'compatibility_resolutions' => [],
            'effects' => [],
            'decay' => [],
            'legacies' => [],
            'memory_feedback' => [],
        ];

        // STEP 1: Evaluate World State
        $worldState = $this->stateEvaluator->evaluate($instances);
        $tickResults['state_evaluation'] = $worldState;

        // STEP 2: Resolve Activation
        // TODO: Get dormant materials from repository
        // $dormantMaterials = $this->repository->getDormantMaterials($worldId);
        // $activations = $this->activationResolver->resolve(
        //     $worldState['pressure_levels'],
        //     $dormantMaterials
        // );
        // $tickResults['activations'] = $activations;

        // STEP 3: Check Compatibility
        $activeMaterials = $instances->where('retired_at', null);
        // $newlyActivated = collect(); // From step 2
        // $resolutions = $this->compatibilityGate->resolve($activeMaterials, $newlyActivated);
        // $tickResults['compatibility_resolutions'] = $resolutions;

        // STEP 4: Apply Effects
        $effectResults = $this->effectApplier->apply($activeMaterials, $worldState['pressure_levels'], $deltaTime);
        $tickResults['effects'] = $effectResults;

        // STEP 5: Process Decay
        $decayResults = [];
        foreach ($instances as $instance) {
            if ($instance->retired_at) {
                continue; // Skip already retired
            }

            $decayResult = $this->decayProcessor->processDecay($instance, $worldState['pressure_levels'], $deltaTime);
            $decayResults[] = $decayResult;

            // Update instance
            $this->repository->updateInstance($instance, $instance->toArray());
        }
        $tickResults['decay'] = $decayResults;

        // STEP 6: Emit Legacy
        $legacyResults = [];
        foreach ($instances as $instance) {
            if ($instance->retired_at || $instance->strength_level > 8) {
                $traces = $this->legacyEmitter->emitTraces($instance);
                
                if (!empty($traces)) {
                    // Append to instance history
                    $history = $instance->historical_traces ?? [];
                    foreach ($traces as $trace) {
                        $history[] = $trace;
                    }
                    $instance->historical_traces = $history;
                    $this->repository->updateInstance($instance, $instance->toArray());

                    $legacyResults[] = [
                        'material_code' => $instance->material->code,
                        'traces' => $traces,
                    ];
                }
            }
        }
        $tickResults['legacies'] = $legacyResults;

        // STEP 7: Memory Feedback Loop
        $feedbackEffects = $this->memoryFeedbackLoop->calculate(
            $instances,
            $worldState['pressure_levels']
        );
        $tickResults['memory_feedback'] = $feedbackEffects;

        // STEP 8: Aggregate Deltas
        // Combine effects from Steps 4, 5, 7
        $allDeltas = [];
        $origins = [];

        // From Effects (Step 4)
        foreach ($effectResults as $result) {
            $deltas = $result['deltas'] ?? [];
            foreach ($deltas as $field => $value) {
                if (!isset($allDeltas[$field])) $allDeltas[$field] = 0;
                $allDeltas[$field] += $value;
                $origins[$field][] = $result['material_code'] ?? 'UNKNOWN';
            }
        }

        // From Decay (Step 5) - usually negative structure/core
        foreach ($decayResults as $result) {
            // Assuming decay result structure. If empty, skip.
            // Decay processor might update instance directly, but should also return state impact
        }

        // From Memory Feedback (Step 7)
        foreach ($feedbackEffects as $field => $value) {
            if (!isset($allDeltas[$field])) $allDeltas[$field] = 0;
            $allDeltas[$field] += $value;
            $origins[$field][] = 'MEMORY_FEEDBACK';
        }

        // STEP 9: Apply Deltas & Save Event
        // Reconstruct current state object (or use passed one if object)
        // stateEvaluator returns array, need object for mutator
        // Ideally we fetch current state object from repo first?
        
        // Ensure we have a WorldState object to mutate
        $currentStateObj = $this->stateRepository->getCurrentState($worldId);
        
        // Apply deltas
        $newStateObj = $this->stateMutator->applyDeltas($currentStateObj, $allDeltas, $origins);
        
        // Save Event
        $this->stateRepository->saveEvent(
            $worldId,
            $epoch,
            $allDeltas,
            $origins,
            $tickResults
        );

        // STEP 10: Snapshot
        if ($this->stateRepository->shouldCreateSnapshot($epoch)) {
            $this->stateRepository->saveSnapshot($newStateObj);
        }

        return $tickResults;
    }

    /**
     * Legacy method: Process single instance tick.
     * Kept for backward compatibility.
     * 
     * @deprecated Use processTick() instead
     */
    public function tick(MaterialInstance $instance, array $worldContext): array
    {
        $results = [
            'id' => $instance->id,
            'material_code' => $instance->material->code,
            'collapsed' => false,
            'effects' => [],
            'traces' => [],
        ];

        // 1. Compatibility Check
        if (!$this->compatibilityGate->isCompatible($instance, $worldContext)) {
            $instance->degradation_level = 100;
            $instance->retired_at = now();
            $results['collapsed'] = true;
            $this->repository->updateInstance($instance, $instance->toArray());
            return $results;
        }

        // 2. Effects (simplified)
        if (!$instance->retired_at) {
            $effectResult = $this->effectApplier->apply(collect([$instance]), []);
            $results['effects'] = $effectResult['deltas'] ?? [];
        }

        // 3. Decay
        $this->decayProcessor->processDecay($instance, []);

        // 4. Legacy
        if ($instance->retired_at || $instance->strength_level > 8) {
            $results['traces'] = $this->legacyEmitter->emitTraces($instance);
            
            $history = $instance->historical_traces ?? [];
            foreach ($results['traces'] as $trace) {
                $history[] = $trace;
            }
            $instance->historical_traces = $history;
        }

        // Save
        $this->repository->updateInstance($instance, $instance->toArray());

        return $results;
    }
}
