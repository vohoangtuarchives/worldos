<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\GetWorldHero;

use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Exception\WorldHeroNotFoundException;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class GetWorldHeroHandler
{
    public function __construct(
        private readonly WorldHeroRepositoryInterface $repository,
    ) {
    }

    public function handle(GetWorldHeroQuery $query): WorldHero
    {
        $hero = $this->repository->findById($query->id);
        if ($hero === null) {
            throw WorldHeroNotFoundException::withId($query->id);
        }
        return $hero;
    }
}
