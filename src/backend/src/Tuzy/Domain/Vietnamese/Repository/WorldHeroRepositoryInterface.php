<?php

declare(strict_types=1);

namespace Tuzy\Domain\Vietnamese\Repository;

use Tuzy\Domain\Vietnamese\Entity\WorldHero;

interface WorldHeroRepositoryInterface
{
    public function findById(string $id): ?WorldHero;

    public function save(WorldHero $worldHero): void;
}
