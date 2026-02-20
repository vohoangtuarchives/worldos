<?php

declare(strict_types=1);

namespace Tuzy\Application\World\CreateWorld;

use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class CreateWorldHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(CreateWorldCommand $command): CreateWorldResult
    {
        $world = World::create($command->name);
        $this->worldRepository->save($world);
        return new CreateWorldResult($world->getId(), $world->getName());
    }
}
