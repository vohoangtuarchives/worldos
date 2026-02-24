<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\Services;

use App\WorldOS\Attractor\Contracts\AttractorRepositoryInterface;
use App\WorldOS\Attractor\Entities\AttractorEntity;
use App\WorldOS\Attractor\ValueObjects\AttractorId;
use App\WorldOS\Attractor\ValueObjects\AttractorType;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\StabilityMetric;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use DateTimeImmutable;

/**
 * Bifurcation Manager — detects and manages civilizational bifurcation points.
 *
 * From RFC-DCE §18.1: Attractor layer uses stability analysis to detect
 * bifurcation (σ ≈ 0 → meta-stable → civilization at a crossroads).
 *
 * From CIVILIZATION_ENGINE_LAW_SPACE §8: Bifurcation occurs when eigenvalue λ = 0.
 *
 * Pure computation — NO side effects beyond entity creation.
 */
final class BifurcationManager
{
    /**
     * Stability threshold below which we consider the system near bifurcation.
     */
    private const BIFURCATION_THRESHOLD = 0.15;

    /**
     * Minimum magnitude for generated attractors.
     */
    private const MIN_ATTRACTOR_MAGNITUDE = 0.3;

    public function __construct(
        private readonly AttractorRepositoryInterface $attractorRepository,
    ) {
    }

    /**
     * Detect if the Universe is near a bifurcation point
     * and generate appropriate attractors.
     *
     * @return AttractorEntity[] Newly created attractors
     */
    public function detectBifurcation(
        UniverseId $universeId,
        WorldStateVector $state,
        StabilityMetric $stability,
    ): array {
        // Only trigger near bifurcation (meta-stable: σ ≈ 0)
        if ($stability->value > self::BIFURCATION_THRESHOLD) {
            return [];
        }

        // Determine which attractor types are relevant based on state
        $candidateTypes = $this->identifyCandidateAttractors($state);

        if (empty($candidateTypes)) {
            return [];
        }

        // Create attractor entities for each candidate
        $newAttractors = [];
        $existingTypes = $this->getExistingAttractorTypes($universeId);

        foreach ($candidateTypes as $type => $magnitude) {
            // Skip if this attractor type already exists for this universe
            if (in_array($type, $existingTypes, true)) {
                continue;
            }

            $attractor = new AttractorEntity(
                id: AttractorId::generate(),
                universeId: $universeId,
                type: $type,
                magnitude: $magnitude,
                basinDepth: $this->calculateBasinDepth($type, $state),
                activationThreshold: 0.5,
                createdAt: new DateTimeImmutable(),
            );

            $this->attractorRepository->save($attractor);
            $newAttractors[] = $attractor;
        }

        return $newAttractors;
    }

    /**
     * Update all active attractors for a Universe based on current state.
     *
     * @return AttractorEntity[] Attractors whose status changed
     */
    public function updateAttractors(
        UniverseId $universeId,
        WorldStateVector $state,
    ): array {
        $attractors = $this->attractorRepository->findByUniverseId($universeId);
        $changed = [];

        foreach ($attractors as $attractor) {
            $oldStatus = $attractor->getStatus();
            $attractor->updatePull($state);

            if ($attractor->getStatus() !== $oldStatus) {
                $this->attractorRepository->save($attractor);
                $changed[] = $attractor;
            }
        }

        return $changed;
    }

    /**
     * Identify which attractor types are candidates based on state vector.
     *
     * @return array<AttractorType, float> type → magnitude
     */
    private function identifyCandidateAttractors(WorldStateVector $state): array
    {
        $candidates = [];

        // High innovation → Technological Singularity pull
        if ($state->innovation > 0.6) {
            $candidates[AttractorType::TECHNOLOGICAL_SINGULARITY->value] = $state->innovation;
            $candidates['_types'][AttractorType::TECHNOLOGICAL_SINGULARITY->value] = AttractorType::TECHNOLOGICAL_SINGULARITY;
        }

        // High order + low innovation → Feudal Stagnation
        if ($state->order > 0.6 && $state->innovation < 0.3) {
            $candidates[AttractorType::FEUDAL_STAGNATION->value] = $state->order * 0.8;
            $candidates['_types'][AttractorType::FEUDAL_STAGNATION->value] = AttractorType::FEUDAL_STAGNATION;
        }

        // High legitimacy + low inequality → Democratic pull
        if ($state->legitimacy > 0.5 && $state->inequality < 0.5) {
            $candidates[AttractorType::DEMOCRATIC_EQUILIBRIUM->value] = $state->legitimacy * 0.7;
            $candidates['_types'][AttractorType::DEMOCRATIC_EQUILIBRIUM->value] = AttractorType::DEMOCRATIC_EQUILIBRIUM;
        }

        // High cohesion → Spiritual Transcendence
        if ($state->cohesion > 0.7) {
            $candidates[AttractorType::SPIRITUAL_TRANSCENDENCE->value] = $state->cohesion * 0.6;
            $candidates['_types'][AttractorType::SPIRITUAL_TRANSCENDENCE->value] = AttractorType::SPIRITUAL_TRANSCENDENCE;
        }

        // High entropy + low cohesion → Collapse Spiral
        if ($state->entropy > 0.7 && $state->cohesion < 0.3) {
            $candidates[AttractorType::COLLAPSE_SPIRAL->value] = $state->entropy;
            $candidates['_types'][AttractorType::COLLAPSE_SPIRAL->value] = AttractorType::COLLAPSE_SPIRAL;
        }

        // Build result: only include sufficiently strong candidates
        $result = [];
        $types = $candidates['_types'] ?? [];
        unset($candidates['_types']);

        foreach ($candidates as $key => $magnitude) {
            if ($magnitude >= self::MIN_ATTRACTOR_MAGNITUDE && isset($types[$key])) {
                $result[$types[$key]->value] = ['type' => $types[$key], 'magnitude' => $magnitude];
            }
        }

        // Return as type → magnitude
        $final = [];
        foreach ($result as $entry) {
            $final[] = ['type' => $entry['type'], 'magnitude' => $entry['magnitude']];
        }

        return array_column($final, 'magnitude', 'type');
    }

    /**
     * Calculate how deep the basin is for a given attractor type.
     */
    private function calculateBasinDepth(AttractorType $type, WorldStateVector $state): float
    {
        return match ($type) {
            AttractorType::TECHNOLOGICAL_SINGULARITY => 0.85,
            AttractorType::FEUDAL_STAGNATION => 0.7,
            AttractorType::DEMOCRATIC_EQUILIBRIUM => 0.75,
            AttractorType::SPIRITUAL_TRANSCENDENCE => 0.9,
            AttractorType::COLLAPSE_SPIRAL => 0.6,
        };
    }

    /**
     * @return string[] Existing attractor type values
     */
    private function getExistingAttractorTypes(UniverseId $universeId): array
    {
        $existing = $this->attractorRepository->findByUniverseId($universeId);

        return array_map(
            fn(AttractorEntity $a) => $a->getType()->value,
            $existing
        );
    }
}
