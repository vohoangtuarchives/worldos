<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\Repository;

use WorldOS\Blueprint\Domain\World\Entity\World;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldId;

/**
 * Repository contract for World Blueprint aggregate.
 * Domain layer — no infrastructure dependencies.
 */
interface WorldRepositoryInterface
{
    /**
     * Persist (insert or update) a World Blueprint.
     */
    public function save(World $world): void;

    /**
     * Find a World Blueprint by its ID.
     * Returns null if not found.
     */
    public function findById(WorldId $id): ?World;

    /**
     * Find all active (non-archived) World Blueprints for a given multiverse.
     *
     * @return World[]
     */
    public function findByMultiverse(string $multiverseId): array;
}
