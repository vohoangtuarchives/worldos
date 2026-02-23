<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\SpawnUniverse;

use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;

/**
 * Handles the SpawnUniverseCommand.
 * Creates a new Universe entity from a World Blueprint and persists it.
 */
final class SpawnUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository
    ) {
    }

    public function handle(SpawnUniverseCommand $command): Universe
    {
        // Spawn with genesis StateVector (all dimensions at default equilibrium)
        $initialStateVector = StateVector::genesis()->toArray();

        $universe = Universe::spawn(
            name:              $command->name,
            worldBlueprintId:  $command->worldBlueprintId,
            initialStateVector: $initialStateVector,
        );

        // Activate the Universe immediately so it can be stepped
        $universe->start();

        $this->universeRepository->save($universe);

        return $universe;
    }
}
