<?php

declare(strict_types=1);

namespace App\Domains\Mutation;

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Events\UniverseMutationCommitted;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Narrative\Planning\ArcType;
use App\Domains\Narrative\Planning\StoryOutcomeDTO;
use Illuminate\Support\Facades\Event;

/**
 * Single boundary for committing story outcome to Universe (Phase 3.4).
 * Arc completion and (future) narrative adapter must use this service only; no direct CosmologyRepository::save for mutations.
 */
class UniverseMutationService
{
    public function __construct(
        private readonly CosmologyRepository $cosmologyRepository,
        private readonly OutcomeQuantizer $quantizer,
        private readonly MutationMapper $mapper,
        private readonly MutationLimiter $limiter,
        private readonly InertiaApplier $inertiaApplier,
    ) {
    }

    /**
     * Commit mutation for the given universe. Applies shadow multiplier when !outcome->isConfirmed.
     */
    public function commit(string $universeId, StoryOutcomeDTO $outcome, ?ArcType $arcType = null, ?string $worldId = null): void
    {
        $universe = $this->cosmologyRepository->find($universeId);
        if ($universe === null) {
            throw new \RuntimeException("Universe not found: {$universeId}");
        }

        $multiplier = $this->quantizer->magnitudeMultiplier($outcome->isConfirmed);
        $rawDelta = $this->mapper->mapToDelta($outcome, $arcType);
        $scaled = WorldStateVector::fromArray(
            array_map(fn ($v) => $v * $multiplier, $rawDelta->getAll())
        );
        $limited = $this->limiter->limit($scaled);

        $state = $universe->getState();
        $stabilityFactor = $this->stabilityFactorFromState($state);
        $delta = $this->inertiaApplier->apply($limited, $stabilityFactor);

        $universe->applyMutation($delta);
        $this->cosmologyRepository->save($universe, $worldId);

        Event::dispatch(new UniverseMutationCommitted($universeId, $outcome, $delta));
    }

    /**
     * Build mutation preview (delta) without committing. For API preview response.
     */
    public function preview(string $universeId, StoryOutcomeDTO $outcome, ?ArcType $arcType = null): array
    {
        $universe = $this->cosmologyRepository->find($universeId);
        if ($universe === null) {
            throw new \RuntimeException("Universe not found: {$universeId}");
        }

        $multiplier = $this->quantizer->magnitudeMultiplier($outcome->isConfirmed);
        $rawDelta = $this->mapper->mapToDelta($outcome, $arcType);
        $scaled = WorldStateVector::fromArray(
            array_map(fn ($v) => $v * $multiplier, $rawDelta->getAll())
        );
        $limited = $this->limiter->limit($scaled);
        $state = $universe->getState();
        $stabilityFactor = $this->stabilityFactorFromState($state);
        $delta = $this->inertiaApplier->apply($limited, $stabilityFactor);

        return [
            'mutation_preview' => $delta->getAll(),
            'phase_change' => false,
        ];
    }

    private function stabilityFactorFromState(WorldStateVector $state): float
    {
        $order = $state->getOrder();
        $cohesion = $state->getCohesion();
        return ($order + $cohesion) / 2.0;
    }
}
