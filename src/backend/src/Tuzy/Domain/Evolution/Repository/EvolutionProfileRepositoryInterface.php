<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Repository;

use Tuzy\Domain\Evolution\Entity\EvolutionProfile;

interface EvolutionProfileRepositoryInterface
{
    /** @return list<EvolutionProfile> */
    public function findAll(): array;

    public function findById(string $id): ?EvolutionProfile;

    public function save(EvolutionProfile $profile): void;
}
