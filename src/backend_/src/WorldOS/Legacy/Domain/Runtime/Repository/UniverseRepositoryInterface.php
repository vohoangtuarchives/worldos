<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Runtime\Repository;

use WorldOS\Legacy\Domain\Runtime\Entity\Universe;

interface UniverseRepositoryInterface
{
    /** @return list<Universe> */
    public function findAll(): array;

    public function findById(string $id): ?Universe;

    /** @return list<Universe> */
    public function findByWorldId(string $worldId): array;

    public function save(Universe $universe): void;

    public function delete(string $id): void;
}
