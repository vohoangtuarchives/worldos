<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Universe\Repository;

use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;

/**
 * Universe Repository interface.
 * Keeps the Domain completely free of infrastructure concerns.
 */
interface UniverseRepositoryInterface
{
    public function findById(UniverseId $id): ?Universe;

    /**
     * @return Universe[]
     */
    public function findByGeneration(string $generationId): array;

    /**
     * @return Universe[]
     */
    public function findChildren(string $parentUniverseId): array;

    /**
     * @return Universe[]
     */
    public function findByMultiverse(string $multiverseId): array;

    public function save(Universe $universe): void;

    public function delete(UniverseId $id): void;
}
