<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\Repository;

use Tuzy\Domain\Cosmology\Entity\UniverseStyle;

interface UniverseStyleRepositoryInterface
{
    public function findById(string $id): ?UniverseStyle;

    public function save(UniverseStyle $universeStyle): void;
}
