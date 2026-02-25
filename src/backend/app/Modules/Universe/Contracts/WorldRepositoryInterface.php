<?php

declare(strict_types=1);

namespace App\Modules\Universe\Contracts;

use App\Modules\Universe\Entities\WorldEntity;
use App\Modules\Universe\ValueObjects\WorldId;

/**
 * World Repository Contract — Domain layer interface.
 *
 * Infrastructure (Eloquent) implements this contract.
 */
interface WorldRepositoryInterface
{
    public function save(WorldEntity $entity): void;

    public function findById(WorldId $id): ?WorldEntity;

    /**
     * @return WorldEntity[]
     */
    public function findAll(): array;

    /**
     * @return WorldEntity[]
     */
    public function findByStatus(string $status): array;
}
