<?php

declare(strict_types=1);

namespace App\WorldOS\Style\Contracts;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\Entities\UniverseStyleEntity;

interface StyleRepositoryInterface
{
    public function findById(string $id): ?UniverseStyleEntity;

    public function save(UniverseStyleEntity $style): void;

    public function findActiveByUniverseId(UniverseId $universeId): ?UniverseStyleEntity;

    /**
     * @return UniverseStyleEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array;
}
