<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\UpdateUniverse;

use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Exception\UniverseNotFoundException;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class UpdateUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    public function handle(UpdateUniverseCommand $command): void
    {
        $existing = $this->universeRepository->findById($command->id);
        if ($existing === null) {
            throw UniverseNotFoundException::withId($command->id);
        }
        $universe = Universe::create(
            $command->name,
            $command->id,
            $command->age,
            $command->status,
            $existing->getStateVector(),
            $command->entropy,
            $command->stabilityIndex
        );
        $this->universeRepository->save($universe);
    }
}
