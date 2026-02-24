<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Services;

use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use LogicException;

/**
 * Universe Mutation Service — the single gate for runtime state changes.
 *
 * From docs §16.2: "Chỉ UniverseMutationService được commit thay đổi runtime."
 *
 * All influence forces MUST pass through this service to be applied
 * to a Universe's state vector. This ensures:
 * - Magnitude limiting (no single tick > MAX_DELTA_PER_TICK)
 * - State invariant protection
 * - Audit trail
 *
 * Pure PHP except for the guard — NO Eloquent, NO repository calls.
 */
final class UniverseMutationService
{
    /**
     * Maximum total magnitude of VectorForce per single mutation.
     * Prevents runaway mutations from any single source.
     */
    private const MAX_DELTA_PER_TICK = 0.15;

    /**
     * Apply an influence force to a Universe's state vector.
     *
     * @throws LogicException if Universe cannot be mutated
     */
    public function applyInfluence(
        UniverseEntity $universe,
        VectorForce $force,
    ): WorldStateVector {
        // Guard: only tickable universes can be mutated
        if (!$universe->canTick()) {
            throw new LogicException(
                "Cannot mutate Universe [{$universe->getId()}]: not in tickable state"
            );
        }

        // Magnitude limiting
        $limitedForce = $this->limitMagnitude($force);

        // Apply force to current state
        $currentState = $universe->getStateVector();
        $newState = $limitedForce->applyTo($currentState);

        return $newState;
    }

    /**
     * Limit the magnitude of a VectorForce to prevent runaway mutations.
     */
    private function limitMagnitude(VectorForce $force): VectorForce
    {
        $magnitude = $force->magnitude();

        if ($magnitude <= self::MAX_DELTA_PER_TICK) {
            return $force;
        }

        $scaleFactor = self::MAX_DELTA_PER_TICK / $magnitude;

        return $force->scale($scaleFactor);
    }
}
