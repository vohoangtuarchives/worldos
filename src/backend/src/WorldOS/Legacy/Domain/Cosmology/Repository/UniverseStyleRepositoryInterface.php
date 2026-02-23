<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\Repository;

use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;

interface UniverseStyleRepositoryInterface
{
    /** @return list<UniverseStyle> */
    public function findAll(): array;

    public function findById(string $id): ?UniverseStyle;

    public function save(UniverseStyle $universeStyle): void;
}
