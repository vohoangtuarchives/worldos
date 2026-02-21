<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\UpdateUniverse;

use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

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
        $universe = Universe::create($command->name, $command->id);
        $this->universeRepository->save($universe);
    }
}
