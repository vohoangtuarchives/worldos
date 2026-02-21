<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\EnvironmentState;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;
use App\Domains\Cosmology\Services\CosmicEvolutionService; // Added this use statement as per instruction
use Illuminate\Support\Facades\Log; // Added this use statement as per instruction

/**
 * WorldEvolutionPipeline
 *
 * The master orchestrator that evolves all 4 layers in the correct order
 * with proper asymmetric coupling:
 *
 *   CE (Cosmic) →→→ EE (Environment) →→→ CivE (Civilization) →→→ AE (Agent)
 *                ←←← (amplified signal)  ←←← (resonance feedback)  ←←← (perturbation)
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
        private ?\App\Domains\Cosmic\Contracts\AttractorRepositoryInterface $attractorRepository = null,
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
    public function step(WorldSnapshot $current, float $agentPerturbation = 0.0, int $deltaYears = 100, array $externalModifiers = []): WorldSnapshot
    {
        $this->lastStepEvents = [];

        // PHASE 0: Check if we're currently morphing (attractor transition in progress)
        if ($this->isMorphing($current)) {
            $current = $this->stepMorph($current);
        }

        // PHASE 1: Gather feedback from previous step (bottom-up)
        $civilizationResonance = $current->civilization->getResonanceFeedback();
        $civilizationImpact = $current->civilization->environmentalImpact();

        // PHASE 2: Evolve Cosmic Layer (uses civilization resonance as weak feedback)
        $nextCosmic = $this->cosmicService->step(
            $current->cosmic,
            $civilizationResonance,
            $deltaYears
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
        $nextCivilization = $current->civilization->evolve(
            $nextEnvironment,
            $nextCosmic,
            $agentPerturbation,
            $deltaYears,
            $modifiers
        );

        // --- NEW: Drama-First Event Injection & Feedback ---
        
        // 1. Determine Phase
        $phase = $this->phaseEngine->determinePhase($nextCivilization);
        $seed = "world_{$current->year}_{$nextCivilization->year}";
        
        // 2. Generate Events
        $events = $this->eventEngine->generateEvents($nextCivilization, $phase, $seed);
        
        // 2.5 Hero Attractor Check
        $heroEvent = $this->heroAttractor->evaluateSpawn($nextCivilization, $seed);
        if ($heroEvent) {
            $events[] = $heroEvent;
        }
        
        $this->lastStepEvents = array_merge($this->lastStepEvents, $events);
        
        // 3. Apply Feedback (Impact MCS)
        $impacts = $this->eventEngine->applyImpacts($nextCivilization, $events);
        
        // 4. Social Dynamics Evolution
        $evolvedClasses = $this->socialDynamicsService->evolveClasses(
            $nextCivilization->socialClasses,
            $nextCivilization,
            $nextCosmic,
            $deltaYears
        );
        
        $nextCivilization = new CivilizationState(
            culturalEnergy: max(0.0, min(1.0, $nextCivilization->culturalEnergy + ($impacts['cultural_energy'] ?? 0.0))),
            spiritualCohesion: $nextCivilization->spiritualCohesion,
            technologicalLevel: $nextCivilization->technologicalLevel,
            stability: max(0.0, min(1.0, $nextCivilization->stability + ($impacts['stability'] ?? 0.0))),
            prosperity: max(0.0, min(1.0, $nextCivilization->prosperity + ($impacts['prosperity'] ?? 0.0))),
            militaryPressure: max(0.0, min(1.0, $nextCivilization->militaryPressure + ($impacts['military_pressure'] ?? 0.0))),
            externalThreat: $nextCivilization->externalThreat,
            internalEntropy: max(0.0, min(1.0, $nextCivilization->internalEntropy + ($impacts['internal_entropy'] ?? 0.0))),
            resonanceAccumulator: $nextCivilization->resonanceAccumulator,
            resilience: $nextCivilization->resilience,
            year: $nextCivilization->year,
            yearsInPhase: $nextCivilization->yearsInPhase,
            socialClasses: $evolvedClasses
        );

        $nextYear = $current->year + $deltaYears;

        $snapshot = new WorldSnapshot(
            cosmic: $nextCosmic,
            environment: $nextEnvironment,
            civilization: $nextCivilization,
            year: $nextYear,
        );

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
     * Apply feedback from significant events back to the stabilization parameters.
     */
    private function applyBifurcationFeedback(WorldSnapshot $snapshot, array $event): WorldSnapshot
    {
        $civ = $snapshot->civilization;
        
        // Example: Bifurcations always increase internal entropy and strain stability
        $newCiv = new CivilizationState(
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
            socialClasses: $civ->socialClasses
        );

        return new WorldSnapshot(
            cosmic: $snapshot->cosmic,
            environment: $snapshot->environment,
            civilization: $newCiv,
            year: $snapshot->year
        );
    }

    /**
     * Calculate how social classes influence the simulation.
     */
    private function calculateSocialModifiers(CivilizationState $civ): array
    {
        $mods = [
            'efficiency_bonus' => 0.0,
            'stability_modifier' => 0.0,
            'knowledge_growth_factor' => 1.0,
            'entropy_resistance' => 0.0,
        ];

        foreach ($civ->socialClasses as $class) {
            switch ($class->type) {
                case \Tuzy\Domain\Cosmology\Enums\SocialClassType::MERCHANT:
                    // High Merchant power boosts efficiency
                    $mods['efficiency_bonus'] += $class->power * 0.2;
                    break;
                case \Tuzy\Domain\Cosmology\Enums\SocialClassType::WARRIOR:
                    // Disgruntled warriors hurt stability, happy ones help
                    $mods['stability_modifier'] += ($class->contentment - 0.5) * $class->power * 0.1;
                    break;
                case \Tuzy\Domain\Cosmology\Enums\SocialClassType::INTELLECTUAL:
                    // Intellectuals boost knowledge growth
                    $mods['knowledge_growth_factor'] += $class->power * 0.5;
                    break;
                case \Tuzy\Domain\Cosmology\Enums\SocialClassType::NOBILITY:
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
            $current = $this->step($current, 0.0, $deltaYears);
            $trajectory[] = $current;

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
            year: $snapshot->year
        );
    }
}
