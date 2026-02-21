<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\CreateWorldHero;

use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class CreateWorldHeroHandler
{
    public function __construct(
        private readonly WorldHeroRepositoryInterface $repository,
    ) {
    }

    public function handle(CreateWorldHeroCommand $command): CreateWorldHeroResult
    {
        $hero = WorldHero::create($command->name, $command->worldId);
        $this->repository->save($hero);
        return new CreateWorldHeroResult($hero->getId(), $hero->getName(), $hero->getWorldId());
    }
}
