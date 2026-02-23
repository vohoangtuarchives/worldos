<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\Attractor;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldSnapshot;

/**
 * BifurcationManager
 *
 * Handles the critical logic of cosmic regime transitions (bifurcations).
 *
 * Bifurcation condition (from PRD):
 *   F_cosmic(t) + R_feedback(t) > Threshold_bifurcation(current_attractor) â†’ Bifurcation
 *
 * Three types of bifurcation:
 * 1. Minor: Transition between existing attractors (most common)
 * 2. Major: Create a NEW attractor that never existed before (rare)
 * 3. Fracture: Structural release without attractor change (handled in CosmicState::evolve)
 *
 * This manager is deterministic â€” no randomness.
 */
class BifurcationManager
{
    private array $bifurcationHistory = [];

    public function __construct(
        private \WorldOS\Evolution\Domain\Legacy\Contract\AttractorRepositoryInterface $attractorRepository,
        private ?\WorldOS\Evolution\Domain\Legacy\Service\MorphingEngine $morphEngine = null
    ) {
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
        $resonance = $snapshot->civilization ? $snapshot->civilization->getResonanceFeedback() : 0.0;
        $currentAttractor = $this->attractorRepository->findByCode($cosmicState->currentAttractor);

        if (!$currentAttractor) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // Calculate the total bifurcation force
        $cosmicForce = $cosmicState->strain;
        $resonanceForce = $resonance * 0.3; // Civilization contribution (weak coupling)
        $totalForce = $cosmicForce + $resonanceForce;

        if ($totalForce < $currentAttractor->getBifurcationThreshold()) {
            return ['snapshot' => $snapshot, 'bifurcated' => false, 'event' => null];
        }

        // Determine bifurcation type based on how much the force exceeds the threshold
        $excess = $totalForce - $currentAttractor->getBifurcationThreshold();

        if ($excess > 0.5 && $resonance > 0.6) {
            // MAJOR BIFURCATION: Create new attractor (very rare)
            return $this->majorBifurcation($snapshot, $currentAttractor, $totalForce);
        }

        // MINOR BIFURCATION: Transition to existing attractor
        return $this->minorBifurcation($snapshot, $currentAttractor, $totalForce);
    }

    /**
     * Minor bifurcation â€” transition between existing attractors.
     * Deterministic: selects closest attractor by state proximity.
     */
    private function minorBifurcation(WorldSnapshot $snapshot, \WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate $from, float $force): array
    {
        $cosmic = $snapshot->cosmic;
        $targetCode = $this->selectClosestAttractor($cosmic, $from);

        if ($targetCode === $from->getCode()) {
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
            'from' => $from->getCode(),
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
                worldField: $snapshot->worldField,
                worldPhase: $snapshot->worldPhase,
                lifeState: $snapshot->lifeState,
                year: $snapshot->year,
            ),
            'bifurcated' => true,
            'event' => $event,
        ];
    }

    /**
     * Minor bifurcation with morphing engine (new implementation).
     */
    private function minorBifurcationWithMorph(WorldSnapshot $snapshot, \WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate $from, string $targetCode, float $force): array
    {
        $cosmic = $snapshot->cosmic;
        
        // Load attractor aggregates from DB
        $fromAggregate = $from;
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
            'from' => $from->getCode(),
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
                worldField: $snapshot->worldField,
                worldPhase: $snapshot->worldPhase,
                lifeState: $snapshot->lifeState,
                year: $snapshot->year,
            ),
            'bifurcated' => true,
            'event' => $event,
        ];
    }

    /**
     * Major bifurcation â€” create a NEW attractor that never existed.
     * This is the "C" option from the PRD: irreversible regime creation.
     *
     * The new attractor's equilibrium is derived from the current state
     * (the universe "crystallizes" around its current configuration).
     */
    private function majorBifurcation(WorldSnapshot $snapshot, \WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate $from, float $force): array
    {
        $cosmic = $snapshot->cosmic;

        // Generate a deterministic code for the new attractor
        $newCode = 'EMERGENT_' . $cosmic->year;

        // The new attractor's equilibrium IS the current state
        $centroid = [
            \WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector::DIMENSION_ENTROPY => round($cosmic->entropy, 2),
            \WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector::DIMENSION_ORDER => round($cosmic->energy, 2),
        ];

        $newAttractorVO = new \WorldOS\Evolution\Domain\Legacy\ValueObject\Attractor(
            $newCode,
            $centroid,
            $centroid
        );

        $newAttractor = \WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate::fromAttractor($newAttractorVO, $cosmic->year);

        // Register the new attractor
        $this->attractorRepository->save($newAttractor);

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
            'from' => $from->getCode(),
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
                worldField: $snapshot->worldField,
                worldPhase: $snapshot->worldPhase,
                lifeState: $snapshot->lifeState,
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
    private function selectClosestAttractor(CosmicState $state, \WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate $current): string
    {
        $bestScore = PHP_FLOAT_MAX;
        $bestCode = $current->getCode();

        $catalog = Attractor::catalog();

        foreach ($catalog as $candidateCode => $candidate) {
            if ($candidateCode === $current->getCode()) continue;

            $centroid = $candidate->getCentroid();
            $equilibriumEntropy = $centroid[\WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector::DIMENSION_ENTROPY] ?? 0.5;
            $equilibriumEnergy = $centroid[\WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector::DIMENSION_ORDER] ?? 0.5;

            $score = abs($state->entropy - $equilibriumEntropy)
                   + abs($state->energy - $equilibriumEnergy);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestCode = $candidateCode;
            }
        }

        return $bestCode;
    }

    public function getHistory(): array
    {
        return $this->bifurcationHistory;
    }

}



