<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\WorldSnapshot;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\EnvironmentState;
use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Illuminate\Support\Facades\Log;
use Tuzy\Domain\Evolution\Enum\CivilizationLifecycleState;
use Tuzy\Domain\Evolution\Enum\WorldPhase;
use Tuzy\Domain\Evolution\Enum\CivilizationPhase;

/**
 * WorldEvolutionPipeline
 *
 * The master orchestrator that evolves all 4 layers in the correct order
 * with proper asymmetric coupling:
 *
 *   CE (Cosmic) â†’â†’â†’ EE (Environment) â†’â†’â†’ CivE (Civilization) â†’â†’â†’ AE (Agent)
 *                â†â†â† (amplified signal)  â†â†â† (resonance feedback)  â†â†â† (perturbation)
 *
 * Forward pass: Top-down influence (cosmic pressures environment, environment constrains civilization)
 * Backward pass: Bottom-up feedback (civilization resonance feeds back to cosmic via environment amplification)
 *
 * The asymmetry: forward coupling is STRONG, backward coupling is WEAK.
 */
class WorldEvolutionPipeline
{
    /**
     * Events emitted during the last step (bifurcations, tipping points, etc.)
     * @var array<array>
     */
    private array $lastStepEvents = [];

    public function __construct(
        private CosmicEvolutionService $cosmicService,
        private BifurcationManager $bifurcationManager,
        private SocialDynamicsService $socialDynamicsService,
        private PhaseEngine $phaseEngine,
        private EventEngine $eventEngine,
        private HeroAttractor $heroAttractor,
        private InternalPressureCalculator $pressureCalculator,
        private FieldReactionEngine $fieldReactionEngine,
        private DynamicsAnalyzer $dynamicsAnalyzer,
        private \Tuzy\Domain\Material\Service\MaterialEvolutionService $materialService,
        private \Tuzy\Domain\Evolution\Contract\EntropyLedgerInterface $entropyLedger,
        private PerturbationGenerator $perturbationGenerator, // NEW
        private ?\Tuzy\Domain\Evolution\Contract\AttractorRepositoryInterface $attractorRepository = null,
        private ?MorphingEngine $morphEngine = null
    ) {}

    /**
     * Evolve the entire world by one time step.
     *
     * @param WorldSnapshot $current Current world state
     * @param float $agentPerturbation Micro-perturbation from agent layer (0.0 to 0.1)
     * @param int $deltaYears Time step in years
     * @return WorldSnapshot The next world state
     */
    public function step(\Tuzy\Domain\Cosmology\Entity\World $world, WorldSnapshot $current, float $agentPerturbation = 0.0, int $deltaYears = 100, array $externalModifiers = []): WorldSnapshot
    {
        $laws = $world->getPhysicalLaws();
        $this->lastStepEvents = [];

        // PHASE 0.1: Field Evolution (The Persistent Layer)
        $seed = "world_{$current->year}_{$deltaYears}";
        $perturbations = $this->perturbationGenerator->generatePerturbations($current->worldField, $current->civilization, $seed);
        
        $nextField = $current->worldField;
        foreach ($perturbations as $perturbation) {
            $nextField = $nextField->withShift($perturbation['shift']);
            $this->lastStepEvents[] = array_merge($perturbation, [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'intensity' => 1.0
            ]);
        }

        // PHASE 0.2: Lifecycle Status Handling
        $isExtinct = in_array($current->civilization->lifecycleState, [
            CivilizationLifecycleState::EXTINCT,
            CivilizationLifecycleState::DORMANT
        ]);

        if ($isExtinct) {
            // Even if extinct, environment and field still evolve
            $nextCosmic = $this->cosmicService->step($laws, $current->cosmic, 0.0, $deltaYears, 0.0);
            $nextEnvironment = $current->environment->evolve($nextCosmic, 0.0, $deltaYears);
            
            // Check for Emergence
            $stateMachine = new HistoryStateMachine();
            if ($stateMachine->checkEmergence($nextField, $nextEnvironment, $seed)) {
                $civ = $stateMachine->spawnNewCivilization($nextField, $nextEnvironment, $current->year + $deltaYears, $current->civilization->getResidual());
                // Transition to Civilizational Age
                return new WorldSnapshot($nextCosmic, $nextEnvironment, $civ, $nextField, WorldPhase::CIVILIZATIONAL_AGE, $current->lifeState->evolve($nextEnvironment->getHabitability(), $nextField->influenceVector['chaos'], $deltaYears), $current->year + $deltaYears);
            }

            return new WorldSnapshot($nextCosmic, $nextEnvironment, $current->civilization, $nextField, $current->worldPhase, $current->lifeState->evolve($nextEnvironment->getHabitability(), $nextField->influenceVector['chaos'], $deltaYears), $current->year + $deltaYears);
        }

        // PHASE 0: Check if we're currently morphing (attractor transition in progress)
        if ($this->isMorphing($current)) {
            $current = $this->stepMorph($current);
        }

        // PHASE 1: Gather feedback from previous step (bottom-up)
        $civilizationResonance = $current->civilization->getResonanceFeedback();
        $civilizationImpact = $current->civilization->environmentalImpact();
        $civilizationEntropyFeedback = $current->civilization->internalEntropy;

        // PHASE 2: Evolve Cosmic Layer (uses civilization resonance and entropy as feedback)
        $nextCosmic = $this->cosmicService->step(
            $laws,
            $current->cosmic,
            $civilizationResonance,
            $deltaYears,
            $civilizationEntropyFeedback
        );

        // PHASE 3: Evolve Environment Layer (receives cosmic pressure, civilization impact)
        $nextEnvironment = $current->environment->evolve(
            $nextCosmic,
            $civilizationImpact,
            $deltaYears
        );

        // PHASE 4: Social Modifiers (Pre-evolution influence)
        $internalModifiers = $this->calculateSocialModifiers($current->civilization);
        
        // Merge internal and external modifiers
        $modifiers = array_merge($internalModifiers, $externalModifiers);
        
        // Handling numeric merges for matching keys if necessary (simple merge overwrites, but we might want additive)
        foreach ($externalModifiers as $key => $value) {
            if (isset($internalModifiers[$key])) {
                $modifiers[$key] = $internalModifiers[$key] + $value;
            }
        }

        // PHASE 5: Evolve Civilization Layer
        $totalPower = $this->materialService->getTotalFactionPower();
        
        $nextCivilization = $current->civilization->evolve(
            $laws,
            $nextEnvironment,
            $nextCosmic,
            $agentPerturbation,
            $deltaYears,
            $modifiers,
            $totalPower
        );

        // --- RECORD LEDGER ---
        $this->entropyLedger->record(
            $world->getId(),
            'INTERNAL_EVOLUTION',
            $nextCivilization->internalEntropy - $current->civilization->internalEntropy,
            $current->year + $deltaYears,
            [
                'energy_level' => $nextCivilization->energy,
                'total_power' => $totalPower,
                'macro_state' => $this->phaseEngine->determinePhase($nextCivilization)->value
            ],
            $world->getSagaId()
        );

        // --- NEW: Drama-First Event Injection & Feedback ---
        
        // 0. Calculate Pressures (feedback system)
        // Wrap current in a temp WorldState aggregate to use its helper methods
        $tempWorld = new \Tuzy\Domain\Evolution\Entity\WorldState($world->getId(), $nextCosmic, $nextEnvironment, $current->year + $deltaYears);
        $pressures = $this->pressureCalculator->calculatePressure($nextCivilization, $tempWorld);

        // 0.1 Calculate Field Dynamics
        $curvature = $this->dynamicsAnalyzer->calculateCurvature($nextCivilization, $current->civilization);
        
        if (config('app.debug')) {
             // We can't easily access $this->output here, but we can add it to the event log
        }

        // 1. Determine Phase (DYNAMICALLY)
        $phase = $this->phaseEngine->determinePhase($nextCivilization, $current->civilization, $curvature);
        $seed = "world_{$current->year}_{$nextCivilization->year}";
        
        // 2.6 Determine Lifecycle & Macro State
        $stateMachine = new \Tuzy\Domain\Evolution\Service\HistoryStateMachine();
        $lifecycleState = $stateMachine->determineLifecycleState($nextCivilization);
        $macroState = $stateMachine->determineMacroState($nextCivilization);
        
        // 2.7 Handle Extinction early
        if ($lifecycleState === CivilizationLifecycleState::EXTINCT) {
            $this->lastStepEvents[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'civilization_extinction',
                'description' => 'TOTAL EXTINCTION: No traces of organized civilization remain.',
                'intensity' => 1.0,
            ];
            
            return new WorldSnapshot(
                cosmic: $nextCosmic,
                environment: $nextEnvironment,
                civilization: new CivilizationSnapshot(
                    culturalEnergy: $nextCivilization->culturalEnergy,
                    spiritualCohesion: $nextCivilization->spiritualCohesion,
                    technologicalLevel: $nextCivilization->technologicalLevel,
                    stability: $nextCivilization->stability,
                    prosperity: $nextCivilization->prosperity,
                    militaryPressure: $nextCivilization->militaryPressure,
                    externalThreat: $nextCivilization->externalThreat,
                    internalEntropy: $nextCivilization->internalEntropy,
                    legitimacy: $nextCivilization->legitimacy,
                    eliteCohesion: $nextCivilization->eliteCohesion,
                    inequality: $nextCivilization->inequality,
                    resonanceAccumulator: $nextCivilization->resonanceAccumulator,
                    resilience: $nextCivilization->resilience,
                    year: $nextCivilization->year,
                    yearsInPhase: $current->civilization->yearsInPhase + $deltaYears,
                    historyPhase: $phase, 
                    powerStage: $nextCivilization->powerStage,
                    fieldCurvature: $curvature,
                    energy: $nextCivilization->energy,
                    socialClasses: $nextCivilization->socialClasses,
                    residual: $nextCivilization->getResidual(),
                    lifecycleState: CivilizationLifecycleState::EXTINCT
                ),
                worldField: $nextField,
                worldPhase: WorldPhase::POST_CATASTROPHE, // Shift to post-catastrophe
                lifeState: $current->lifeState->evolve($nextEnvironment->getHabitability(), $nextField->influenceVector['chaos'], $deltaYears),
                year: $current->year + $deltaYears
            );
        }

        // 2.8 Generate Reactions ONLY if not extinct
        $events = $this->fieldReactionEngine->generateReactions(
            $nextCivilization,
            $current->civilization,
            $phase,
            $seed,
            $pressures,
            $totalPower
        );
        
        // 2.9 Hero Attractor Check
        $heroEvent = $this->heroAttractor->evaluateSpawn($nextCivilization, $seed);
        if ($heroEvent) {
            $events[] = $heroEvent;
        }

        // 2.10 Golden Transcendence Check
        $narrativeEngine = new \Tuzy\Domain\Evolution\Service\NarrativeEngine();
        $transcendenceEvent = $narrativeEngine->evaluateTranscendence($nextCivilization, $seed);
        $isTranscendence = false;
        if ($transcendenceEvent) {
            $events[] = $transcendenceEvent;
            $isTranscendence = true;
            // Force phase to Illumination immediately
            $phase = \Tuzy\Domain\Evolution\Enum\CivilizationPhase::ILLUMINATION;
        }

        if ($lifecycleState === CivilizationLifecycleState::COLLAPSE || $macroState === \Tuzy\Domain\Evolution\Service\HistoryStateMachine::STATE_COLLAPSE) {
             $this->lastStepEvents[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'civilization_collapse',
                'description' => 'CRITICAL ENTROPY/LOW ENERGY: The systemic structure of the world has disintegrated.',
                'intensity' => 1.0,
            ];
            $nextCivilization = $stateMachine->applyCollapse($nextCivilization);
            $lifecycleState = $nextCivilization->lifecycleState; // Should be EMERGENCE after collapse
            
            // Wipe Material Memory for all factions (Historical trauma reset)
            $this->materialService->wipeAllMemories();
        }
        
        $this->lastStepEvents = array_merge($this->lastStepEvents, $events);
        
        // 3. Apply Feedback (Impact MCS)
        $impacts = $this->eventEngine->applyImpacts($nextCivilization, $events);

        // 3.1. Detect Phase Transition
        $currentPhase = $current->civilization->historyPhase;
        $nextPhase = $isTranscendence ? \Tuzy\Domain\Evolution\Enum\CivilizationPhase::ILLUMINATION : $this->phaseEngine->determinePhase($nextCivilization);
        
        // If we are already illuminated, keep it until tension gets too high
        if ($currentPhase === \Tuzy\Domain\Evolution\Enum\CivilizationPhase::ILLUMINATION && !$isTranscendence) {
            if ($nextCivilization->narrativeTension > 0.4 || $nextCivilization->internalEntropy > 0.5) {
                // Fall from grace
                $nextPhase = $this->phaseEngine->determinePhase($nextCivilization);
            } else {
                // Stay illuminated
                $nextPhase = \Tuzy\Domain\Evolution\Enum\CivilizationPhase::ILLUMINATION;
            }
        }
        
        $phaseChanged = ($currentPhase !== $nextPhase);

        if ($phaseChanged) {
            $this->lastStepEvents[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'phase_transition',
                'from' => $currentPhase->value,
                'to' => $nextPhase->value,
                'name' => "Era Shift: " . $nextPhase->label(),
                'intensity' => 1.0,
                'description' => "Văn minh đã chuyển dịch trạng thái từ " . $currentPhase->label() . " sang " . $nextPhase->label(),
                'success' => true,
            ];
        }
        
        // 4. Social Dynamics Evolution
        $evolvedClasses = $this->socialDynamicsService->evolveClasses(
            $nextCivilization->socialClasses,
            $nextCivilization,
            $nextCosmic,
            $deltaYears
        );
        
        $nextCivilization = new CivilizationSnapshot(
            culturalEnergy: max(0.0, min(1.0, $nextCivilization->culturalEnergy + ($impacts['cultural_energy'] ?? 0.0))),
            spiritualCohesion: $nextCivilization->spiritualCohesion,
            technologicalLevel: $nextCivilization->technologicalLevel,
            stability: max(0.0, min(1.0, $nextCivilization->stability + ($impacts['stability'] ?? 0.0))),
            prosperity: max(0.0, min(1.0, $nextCivilization->prosperity + ($impacts['prosperity'] ?? 0.0))),
            militaryPressure: max(0.0, min(1.0, $nextCivilization->militaryPressure + ($impacts['military_pressure'] ?? 0.0))),
            externalThreat: $nextCivilization->externalThreat,
            internalEntropy: max(0.0, min(1.0, $nextCivilization->internalEntropy + ($impacts['internal_entropy'] ?? 0.0))),
            legitimacy: max(0.0, min(1.0, $nextCivilization->legitimacy + ($impacts['legitimacy'] ?? 0.0))),
            eliteCohesion: max(0.0, min(1.0, $nextCivilization->eliteCohesion + ($impacts['elite_cohesion'] ?? 0.0))),
            inequality: max(0.0, min(1.0, $nextCivilization->inequality + ($impacts['inequality'] ?? 0.0))),
            resonanceAccumulator: $nextCivilization->resonanceAccumulator,
            resilience: $nextCivilization->resilience,
            year: $nextCivilization->year,
            yearsInPhase: $phaseChanged ? 0 : $current->civilization->yearsInPhase + $deltaYears,
            historyPhase: $nextPhase,
            powerStage: $nextCivilization->powerStage,
            fieldCurvature: $curvature,
            socialClasses: $evolvedClasses,
            residual: $this->applyTrauma($nextCivilization->getResidual(), $impacts['trauma'] ?? []),
            lifecycleState: $lifecycleState,
            narrativeTension: $nextCivilization->narrativeTension,
            shortWaveTension: $nextCivilization->shortWaveTension,
            longWaveTension: $nextCivilization->longWaveTension,
            heroCount: $nextCivilization->heroCount
        );

        $nextYear = $current->year + $deltaYears;

        $snapshot = new WorldSnapshot(
            cosmic: $nextCosmic,
            environment: $nextEnvironment,
            civilization: $nextCivilization,
            worldField: $nextField,
            worldPhase: $current->worldPhase, // Keep current phase unless event shifts it
            lifeState: $current->lifeState->evolve($nextEnvironment->getHabitability(), $nextField->influenceVector['chaos'], $deltaYears),
            year: $nextYear,
        );

        // PHASE 5: Material Synchronization (The Physical Body)
        $this->materialService->sync($nextCivilization, $nextCosmic);

        // 3. Feedback Loop: Apply event impacts BACK to state
        // (This would be more complex in final implementation)

        // PHASE 6: Check for bifurcation
        $bifResult = $this->bifurcationManager->evaluate($snapshot);
        if ($bifResult['bifurcated']) {
            $snapshot = $bifResult['snapshot'];
            $this->lastStepEvents[] = $bifResult['event'];
            
            // Apply Bifurcation impact to state
            $snapshot = $this->applyBifurcationFeedback($snapshot, $bifResult['event']);
        }

        return $snapshot;
    }

    /**
     * Apply trauma impacts to the residual memory.
     */
    private function applyTrauma(\Tuzy\Domain\Evolution\ValueObject\CivilizationResidual $residual, array $trauma): \Tuzy\Domain\Evolution\ValueObject\CivilizationResidual
    {
        $newResidual = clone $residual;
        if (isset($trauma['war'])) $newResidual->addTrauma($trauma['war'], 'war');
        if (isset($trauma['metaphysical'])) $newResidual->addTrauma($trauma['metaphysical'], 'metaphysical');
        if (isset($trauma['social'])) $newResidual->addTrauma($trauma['social'], 'social');
        
        // Decay trauma naturally each step
        $newResidual->decay();
        
        return $newResidual;
    }

    /**
     * Apply feedback from significant events back to the stabilization parameters.
     */
    private function applyBifurcationFeedback(WorldSnapshot $snapshot, array $event): WorldSnapshot
    {
        $civ = $snapshot->civilization;
        
        // Example: Bifurcations always increase internal entropy and strain stability
        $newCiv = new CivilizationSnapshot(
            culturalEnergy: $civ->culturalEnergy,
            spiritualCohesion: $civ->spiritualCohesion,
            technologicalLevel: $civ->technologicalLevel,
            stability: max(0.0, $civ->stability - 0.05),
            prosperity: $civ->prosperity,
            militaryPressure: $civ->militaryPressure,
            externalThreat: $civ->externalThreat,
            internalEntropy: min(1.0, $civ->internalEntropy + 0.1),
            resonanceAccumulator: $civ->resonanceAccumulator,
            resilience: $civ->resilience,
            year: $civ->year,
            yearsInPhase: $civ->yearsInPhase,
            historyPhase: $civ->historyPhase,
            powerStage: $civ->powerStage,
            socialClasses: $civ->socialClasses
        );

        return new WorldSnapshot(
            cosmic: $snapshot->cosmic,
            environment: $snapshot->environment,
            civilization: $newCiv,
            worldField: $snapshot->worldField,
            worldPhase: $snapshot->worldPhase,
            lifeState: $snapshot->lifeState,
            year: $snapshot->year
        );
    }

    /**
     * Calculate how social classes influence the simulation.
     */
    private function calculateSocialModifiers(CivilizationSnapshot $civ): array
    {
        $mods = [
            'efficiency_bonus' => 0.0,
            'stability_modifier' => 0.0,
            'knowledge_growth_factor' => 1.0,
            'entropy_resistance' => 0.0,
        ];

        foreach ($civ->socialClasses as $class) {
            switch ($class->type) {
                case \Tuzy\Domain\Evolution\Enum\SocialClassType::MERCHANT:
                    // High Merchant power boosts efficiency
                    $mods['efficiency_bonus'] += $class->power * 0.2;
                    break;
                case \Tuzy\Domain\Evolution\Enum\SocialClassType::WARRIOR:
                    // Disgruntled warriors hurt stability, happy ones help
                    $mods['stability_modifier'] += ($class->contentment - 0.5) * $class->power * 0.1;
                    break;
                case \Tuzy\Domain\Evolution\Enum\SocialClassType::INTELLECTUAL:
                    // Intellectuals boost knowledge growth
                    $mods['knowledge_growth_factor'] += $class->power * 0.5;
                    break;
                case \Tuzy\Domain\Evolution\Enum\SocialClassType::NOBILITY:
                    // Nobility provides entropy resistance (preserving order)
                    $mods['entropy_resistance'] += $class->power * 0.15;
                    break;
            }
        }

        return $mods;
    }

    /**
     * Run a full simulation for N steps.
     *
     * @param WorldSnapshot $initial Starting state
     * @param int $steps Number of steps
     * @param int $deltaYears Years per step
     * @return array{trajectory: array<WorldSnapshot>, events: array<array>}
     */
    public function simulate(WorldSnapshot $initial, int $steps, int $deltaYears = 100): array
    {
        $trajectory = [$initial];
        $allEvents = [];
        $current = $initial;

        for ($i = 0; $i < $steps; $i++) {
            // Need a way to get world for simulation, assuming it's passed or available
            // For now, assume step requires world
            throw new \Exception("WorldEvolutionPipeline::simulate requires World entity but it's not yet refactored to take it as argument.");
            if (!empty($this->lastStepEvents)) {
                $allEvents = array_merge($allEvents, $this->lastStepEvents);
            }
        }

        return [
            'trajectory' => $trajectory,
            'events' => $allEvents,
        ];
    }

    /**
     * Get events emitted during the last step.
     */
    public function getLastStepEvents(): array
    {
        return $this->lastStepEvents;
    }

    /**
     * Get the bifurcation manager (for inspecting registry/history).
     */
    public function getBifurcationManager(): BifurcationManager
    {
        return $this->bifurcationManager;
    }

    /**
     * Check if the current snapshot is in a morphing state.
     */
    private function isMorphing(WorldSnapshot $snapshot): bool
    {
        return $snapshot->cosmic->morphTargetCentroid !== null;
    }

    /**
     * Step the morph: Update cosmic centroid toward target using damped oscillation.
     */
    private function stepMorph(WorldSnapshot $current): WorldSnapshot
    {
        if (!$this->attractorRepository || !$this->morphEngine) {
            // No morph engine available, clear morph state
            return $this->clearMorphState($current);
        }

        $elapsed = $current->year - ($current->cosmic->morphStartTick ?? $current->year);

        // Get target attractor code from incarnation
        $attractorCode = $current->cosmic->currentAttractor;
        
        // For now, use a simple incarnation lookup (this could be enhanced)
        $attractor = $this->attractorRepository->findByCode($attractorCode);
        if (!$attractor) {
            return $this->clearMorphState($current);
        }

        $incarnation = $attractor->getCurrentIncarnation();
        if (!$incarnation) {
            return $this->clearMorphState($current);
        }

        // Step the morph using MorphingEngine
        $newCentroid = $this->morphEngine->stepMorph(
            $incarnation,
            $current->cosmic->morphTargetCentroid,
            $elapsed
        );

        // Check if morph is complete
        $isComplete = $this->morphEngine->isMorphComplete(
            $newCentroid,
            $current->cosmic->morphTargetCentroid
        );

        // Create new cosmic state with morphed centroid
        $newCosmic = new CosmicState(
            entropy: $newCentroid['entropy'] ?? $current->cosmic->entropy,
            energy: $newCentroid['energy'] ?? $current->cosmic->energy,
            causality: $newCentroid['causality'] ?? $current->cosmic->causality,
            strain: $newCentroid['strain'] ?? $current->cosmic->strain,
            stability: $newCentroid['stability'] ?? $current->cosmic->stability,
            currentAttractor: $current->cosmic->currentAttractor,
            year: $current->year,
            currentIncarnationId: $current->cosmic->currentIncarnationId,
            morphTargetCentroid: $isComplete ? null : $current->cosmic->morphTargetCentroid,
            morphStartTick: $isComplete ? null : $current->cosmic->morphStartTick,
            morphIntensity: $current->cosmic->morphIntensity
        );

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $current->environment,
            civilization: $current->civilization,
            worldField: $current->worldField,
            worldPhase: $current->worldPhase,
            lifeState: $current->lifeState,
            year: $current->year
        );
    }

    /**
     * Clear morphing state from a snapshot.
     */
    private function clearMorphState(WorldSnapshot $snapshot): WorldSnapshot
    {
        $newCosmic = new CosmicState(
            entropy: $snapshot->cosmic->entropy,
            energy: $snapshot->cosmic->energy,
            causality: $snapshot->cosmic->causality,
            strain: $snapshot->cosmic->strain,
            stability: $snapshot->cosmic->stability,
            currentAttractor: $snapshot->cosmic->currentAttractor,
            year: $snapshot->cosmic->year,
            currentIncarnationId: $snapshot->cosmic->currentIncarnationId,
            morphTargetCentroid: null,
            morphStartTick: null,
            morphIntensity: 1.0
        );

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            worldField: $snapshot->worldField,
            worldPhase: $snapshot->worldPhase,
            lifeState: $snapshot->lifeState,
            year: $snapshot->year
        );
    }
}




