<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Contracts;

use App\WorldOS\CivilizationMemory\Entities\WorldMythEntity;
use App\WorldOS\CivilizationMemory\ValueObjects\MythId;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

interface MythRepositoryInterface
{
    public function findById(MythId $id): ?WorldMythEntity;

    public function save(WorldMythEntity $myth): void;

    /**
     * @return WorldMythEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array;

    /**
     * @return WorldMythEntity[]
     */
    public function findActiveByUniverseId(UniverseId $universeId): array;
}
