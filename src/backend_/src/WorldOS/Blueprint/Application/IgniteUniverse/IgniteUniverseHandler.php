<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Application\IgniteUniverse;

use DomainException;
use WorldOS\Blueprint\Domain\World\Repository\WorldRepositoryInterface;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldId;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldStatus;
use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;

/**
 * Ignites a new Universe from a SEALED World Blueprint.
 *
 * Responsibilities:
 *  - Load and validate the World Blueprint (must be SEALED).
 *  - Freeze the WorldSignature into the new Universe at Ignite time.
 *  - Persist the Universe via UniverseRepository.
 *
 * This is the canonical entry-point for creating production Universes.
 * Tests / tooling may still use Universe::spawn() directly.
 */
final class IgniteUniverseHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface    $worldRepository,
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {}

    public function handle(IgniteUniverseCommand $command): Universe
    {
        $worldId = WorldId::fromString($command->worldId);

        $world = $this->worldRepository->findById($worldId);

        if ($world === null) {
            throw new DomainException(
                "World Blueprint [{$command->worldId}] not found."
            );
        }

        if (!$world->getStatus()->isSealed()) {
            throw new DomainException(
                "World Blueprint [{$command->worldId}] must be SEALED before igniting a Universe. "
                . "Current status: {$world->getStatus()->value}."
            );
        }

        $universe = Universe::ignite(
            name:               $command->name,
            worldBlueprintId:   $command->worldId,
            worldSignatureHash: $world->getSignature()->getHash(),
            initialStateVector: [],
        );

        $this->universeRepository->save($universe);

        return $universe;
    }
}
