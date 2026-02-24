<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Repository;

use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;

interface EvolutionProfileRepositoryInterface
{
    /** @return list<EvolutionProfile> */
    public function findAll(): array;

    public function findById(string $id): ?EvolutionProfile;

    public function save(EvolutionProfile $profile): void;
}
