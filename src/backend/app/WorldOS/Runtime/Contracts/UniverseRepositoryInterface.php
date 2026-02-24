<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Contracts;

use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\World\ValueObjects\WorldId;

/**
 * Universe Repository Contract — Domain layer interface.
 */
interface UniverseRepositoryInterface
{
    public function save(UniverseEntity $entity): void;

    public function findById(UniverseId $id): ?UniverseEntity;

    /**
     * @return UniverseEntity[]
     */
    public function findByWorldId(WorldId $worldId): array;

    /**
     * @return UniverseEntity[]
     */
    public function findByStatus(string $status): array;

    /**
     * @return UniverseEntity[]
     */
    public function findForks(UniverseId $parentId): array;
}
