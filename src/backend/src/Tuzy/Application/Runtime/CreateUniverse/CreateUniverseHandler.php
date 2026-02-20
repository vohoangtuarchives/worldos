<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\CreateUniverse;

use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class CreateUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    public function handle(CreateUniverseCommand $command): CreateUniverseResult
    {
        $universe = Universe::create($command->name);
        $this->universeRepository->save($universe);
        return new CreateUniverseResult($universe->getId(), $universe->getName());
    }
}
