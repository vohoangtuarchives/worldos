<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Repository;

use Tuzy\Domain\Evolution\Entity\EvolutionProfile;

interface EvolutionProfileRepositoryInterface
{
    public function findById(string $id): ?EvolutionProfile;

    public function save(EvolutionProfile $profile): void;
}
