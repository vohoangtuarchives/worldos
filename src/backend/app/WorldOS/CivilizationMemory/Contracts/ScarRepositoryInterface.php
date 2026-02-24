<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Contracts;

use App\WorldOS\CivilizationMemory\Entities\WorldScarEntity;
use App\WorldOS\CivilizationMemory\ValueObjects\ScarId;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

interface ScarRepositoryInterface
{
    public function findById(ScarId $id): ?WorldScarEntity;

    public function save(WorldScarEntity $scar): void;

    /**
     * @return WorldScarEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array;

    /**
     * Get total scar pressure for a Universe at a given tick.
     */
    public function calculateTotalPressure(UniverseId $universeId, int $currentTick): float;
}
