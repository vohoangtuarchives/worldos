<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\GetHero;

use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Exception\HeroNotFoundException;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;

final class GetHeroHandler
{
    public function __construct(
        private readonly HeroRepositoryInterface $repository,
    ) {
    }

    public function handle(GetHeroQuery $query): Hero
    {
        $hero = $this->repository->findById($query->id);
        if ($hero === null) {
            throw HeroNotFoundException::withId($query->id);
        }
        return $hero;
    }
}
