<?php

declare(strict_types=1);

namespace Tuzy\Application\Vietnamese\CreateWorldHero;

use Tuzy\Domain\Vietnamese\Entity\WorldHero;
use Tuzy\Domain\Vietnamese\Repository\WorldHeroRepositoryInterface;

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
