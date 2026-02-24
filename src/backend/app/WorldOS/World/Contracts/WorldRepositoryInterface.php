<?php

declare(strict_types=1);

namespace App\WorldOS\World\Contracts;

use App\WorldOS\World\Entities\WorldEntity;
use App\WorldOS\World\ValueObjects\WorldId;

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
