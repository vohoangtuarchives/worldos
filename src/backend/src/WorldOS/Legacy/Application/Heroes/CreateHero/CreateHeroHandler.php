<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\CreateHero;

use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;

final class CreateHeroHandler
{
    public function __construct(
        private readonly HeroRepositoryInterface $repository,
    ) {
    }

    public function handle(CreateHeroCommand $command): CreateHeroResult
    {
        $profile = HeroProfile::create('transcendence', 0.5);
        $hero = Hero::create($command->name, $command->worldId, $profile);
        $this->repository->save($hero);
        return new CreateHeroResult($hero->getId(), $hero->getName(), $hero->getWorldId());
    }
}
