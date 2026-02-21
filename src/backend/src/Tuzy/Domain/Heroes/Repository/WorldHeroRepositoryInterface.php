<?php

declare(strict_types=1);

namespace Tuzy\Domain\Heroes\Repository;

use Tuzy\Domain\Heroes\Entity\WorldHero;

interface WorldHeroRepositoryInterface
{
    /** @return list<WorldHero> */
    public function findAll(): array;

    public function findById(string $id): ?WorldHero;

    public function save(WorldHero $worldHero): void;
}
