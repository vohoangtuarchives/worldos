<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use App\Domains\Cosmology\ValueObjects\Attractor;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;

/**
 * BifurcationManager
 *
 * Handles the critical logic of cosmic regime transitions (bifurcations).
 *
 * Bifurcation condition (from PRD):
 *   F_cosmic(t) + R_feedback(t) > Threshold_bifurcation(current_attractor) → Bifurcation
 *
 * Three types of bifurcation:
 * 1. Minor: Transition between existing attractors (most common)
 * 2. Major: Create a NEW attractor that never existed before (rare)
 * 3. Fracture: Structural release without attractor change (handled in CosmicState::evolve)
 *
 * This manager is deterministic — no randomness.
 */
class BifurcationManager
{
    /**
     * Registry of known attractors. Starts with the catalog but can grow.
     * @var array<string, Attractor>
     */
    private array $attractorRegistry;

    /**
     * History of bifurcation events for auditing/narrative.
     * @var array<array{year: int, from: string, to: string, type: string, force: float}>
     */
    private array $bifurcationHistory = [];

    public function __construct(
        private ?\App\Domains\Cosmology\Contracts\AttractorRepositoryInterface $attractorRepository = null,
        private ?\App\Domains\Cosmology\Services\MorphingEngine $morphEngine = null
    ) {
        $this->attractorRegistry = Attractor::catalog();
    }

    /**
     * Evaluate whether a bifurcation should occur and apply it.
     *
     * @param WorldSnapshot $snapshot Current world state (all layers)
     * @return array{snapshot: WorldSnapshot, bifurcated: bool, event: ?array}
     */
    public function evaluate(WorldSnapshot $snapshot): array
    {
        $cosmicState = $snapshot->cosmic;
        $resonance = $snapshot->civilization->getResonanceFeedback();
        $currentAttractor = $this->findAttractor($cosmicState->currentAttractor);

        if (!$currentAttractor) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // Calculate the total bifurcation force
        $cosmicForce = $cosmicState->strain;
        $resonanceForce = $resonance * 0.3; // Civilization contribution (weak coupling)
        $totalForce = $cosmicForce + $resonanceForce;

        if ($totalForce < $currentAttractor->bifurcationThreshold) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // Determine bifurcation type based on how much the force exceeds the threshold
        $excess = $totalForce - $currentAttractor->bifurcationThreshold;

        if ($excess > 0.5 && $resonance > 0.6) {
            // MAJOR BIFURCATION: Create new attractor (very rare)
            return $this->majorBifurcation($snapshot, $currentAttractor, $totalForce);
        }

        // MINOR BIFURCATION: Transition to existing attractor
        return $this->minorBifurcation($snapshot, $currentAttractor, $totalForce);
    }

    /**
     * Minor bifurcation — transition between existing attractors.
     * Deterministic: selects closest attractor by state proximity.
     */
    private function minorBifurcation(WorldSnapshot $snapshot, Attractor $from, float $force): array
    {
        $cosmic = $snapshot->cosmic;
        $targetCode = $this->selectClosestAttractor($cosmic, $from);

        if ($targetCode === $from->code) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // If repositories are available, use morphing engine
        if ($this->attractorRepository && $this->morphEngine) {
            return $this->minorBifurcationWithMorph($snapshot, $from, $targetCode, $force);
        }

        // Fallback to instant transition (legacy)
        $newCosmic = new CosmicState(
            entropy: $cosmic->entropy,
            energy: $cosmic->energy,
            causality: $cosmic->causality * 0.7,  // Partial release
            strain: $cosmic->strain * 0.3,         // Major strain release
            stability: $cosmic->stability,
            currentAttractor: $targetCode,
            year: $cosmic->year,
        );

        $event = [
            'year' => $cosmic->year,
            'from' => $from->code,
            'to' => $targetCode,
            'type' => 'MINOR_BIFURCATION',
            'force' => round($force, 4),
        ];

        $this->bifurcationHistory[] = $event;

        return [
            'snapshot' => new WorldSnapshot(
                cosmic: $newCosmic,
                environment: $snapshot->environment,
                civilization: $snapshot->civilization,
                year: $snapshot->year,
            ),
            'bifurcated' => true,
            'event' => $event,
        ];
    }

    /**
     * Minor bifurcation with morphing engine (new implementation).
     */
    private function minorBifurcationWithMorph(WorldSnapshot $snapshot, Attractor $from, string $targetCode, float $force): array
    {
        $cosmic = $snapshot->cosmic;
        
        // Load attractor aggregates from DB
        $fromAggregate = $this->attractorRepository->findByCode($from->code);
        $toAggregate = $this->attractorRepository->findByCode($targetCode);

        if (!$fromAggregate || !$toAggregate) {
            // Fallback to legacy if DB not seeded
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        $targetIncarnation = $toAggregate->getCurrentIncarnation();
        if (!$targetIncarnation) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // Close old incarnation
        if ($fromAggregate->getCurrentIncarnationId()) {
            $this->attractorRepository->closeIncarnation(
                $fromAggregate->getCurrentIncarnationId(),
                $snapshot->year
            );
        }

        // Start morph: create new incarnation
        $newInc = $this->morphEngine->startMorph(
            $toAggregate,
            $targetIncarnation->centroidSnapshot,
            1.0
        );

        // Save new incarnation
        $this->attractorRepository->saveIncarnation($newInc);

        // Update attractor's current incarnation
        $toAggregate->setCurrentIncarnationId($newInc->id);
        $this->attractorRepository->save($toAggregate);

        // Create new cosmic state with morphing flag
        $newCosmic = new CosmicState(
            entropy: $cosmic->entropy,
            energy: $cosmic->energy,
            causality: $cosmic->causality * 0.7,
            strain: $cosmic->strain * 0.3,
            stability: $cosmic->stability,
            currentAttractor: $targetCode,
            year: $cosmic->year,
            currentIncarnationId: $newInc->id
        );

        $event = [
            'year' => $cosmic->year,
            'from' => $from->code,
            'to' => $targetCode,
            'type' => 'MINOR_BIFURCATION_MORPH',
            'force' => round($force, 4),
            'incarnation_id' => $newInc->id,
            'morph_intensity' => $newInc->morphIntensity,
        ];

        $this->bifurcationHistory[] = $event;

        return [
            'snapshot' => new WorldSnapshot(
                cosmic: $newCosmic,
                environment: $snapshot->environment,
                civilization: $snapshot->civilization,
                year: $snapshot->year,
            ),
            'bifurcated' => true,
            'event' => $event,
        ];
    }

    /**
     * Major bifurcation — create a NEW attractor that never existed.
     * This is the "C" option from the PRD: irreversible regime creation.
     *
     * The new attractor's equilibrium is derived from the current state
     * (the universe "crystallizes" around its current configuration).
     */
    private function majorBifurcation(WorldSnapshot $snapshot, Attractor $from, float $force): array
    {
        $cosmic = $snapshot->cosmic;

        // Generate a deterministic code for the new attractor
        $newCode = 'EMERGENT_' . $cosmic->year;

        // The new attractor's equilibrium IS the current state
        // (universe stabilizes around where it "broke through")
        $newAttractor = new Attractor(
            code: $newCode,
            name: "Emergent Regime (Year {$cosmic->year})",
            equilibriumEntropy: round($cosmic->entropy, 2),
            equilibriumEnergy: round($cosmic->energy, 2),
            bifurcationThreshold: round($from->bifurcationThreshold * 1.2, 2), // Harder to leave
            transitionsTo: [$from->code, 'EQUILIBRIUM'], // Can return to origin or equilibrium
        );

        // Register the new attractor
        $this->attractorRegistry[$newCode] = $newAttractor;

        $newCosmic = new CosmicState(
            entropy: $cosmic->entropy * 0.85,      // Significant release
            energy: $cosmic->energy,
            causality: $cosmic->causality * 0.5,    // Major causality release
            strain: $cosmic->strain * 0.2,          // Near-complete strain release
            stability: $cosmic->stability,
            currentAttractor: $newCode,
            year: $cosmic->year,
        );

        $event = [
            'year' => $cosmic->year,
            'from' => $from->code,
            'to' => $newCode,
            'type' => 'MAJOR_BIFURCATION',
            'force' => round($force, 4),
            'new_attractor' => $newAttractor->toArray(),
        ];

        $this->bifurcationHistory[] = $event;

        return [
            'snapshot' => new WorldSnapshot(
                cosmic: $newCosmic,
                environment: $snapshot->environment,
                civilization: $snapshot->civilization,
                year: $snapshot->year,
            ),
            'bifurcated' => true,
            'event' => $event,
        ];
    }

    /**
     * Select closest attractor by Euclidean distance from current state.
     * Deterministic: no randomness.
     */
    private function selectClosestAttractor(CosmicState $state, Attractor $current): string
    {
        $bestScore = PHP_FLOAT_MAX;
        $bestCode = $current->code;

        foreach ($current->transitionsTo as $candidateCode) {
            $candidate = $this->findAttractor($candidateCode);
            if (!$candidate) continue;

            $score = abs($state->entropy - $candidate->equilibriumEntropy)
                   + abs($state->energy - $candidate->equilibriumEnergy);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestCode = $candidateCode;
            }
        }

        return $bestCode;
    }

    public function findAttractor(string $code): ?Attractor
    {
        return $this->attractorRegistry[$code] ?? null;
    }

    public function getHistory(): array
    {
        return $this->bifurcationHistory;
    }

    public function getRegistry(): array
    {
        return $this->attractorRegistry;
    }
}
