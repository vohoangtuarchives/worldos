<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\UpdateHero;

use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Exception\HeroNotFoundException;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;

final class UpdateHeroHandler
{
    public function __construct(
        private readonly HeroRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateHeroCommand $command): void
    {
        $existing = $this->repository->findById($command->id);
        if ($existing === null) {
            throw HeroNotFoundException::withId($command->id);
        }
        $hero = Hero::create(
            $command->name, 
            $existing->getWorldId(), 
            $existing->getProfile(), 
            $existing->getState(), 
            $command->id
        );
        $this->repository->save($hero);
    }
}
