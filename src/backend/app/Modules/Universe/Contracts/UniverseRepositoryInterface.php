<?php

declare(strict_types=1);

namespace App\Modules\Universe\Contracts;

use App\Modules\Universe\Entities\UniverseEntity;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Universe\ValueObjects\WorldId;

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
