<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\Repository;

use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\ValueObject\EventType;

interface ChronicleRepositoryInterface
{
    public function save(ChronicleEvent $event): void;

    /**
     * @return ChronicleEvent[]
     */
    public function findByUniverse(string $universeId, int $limit = 50, int $offset = 0): array;

    /**
     * @return ChronicleEvent[]
     */
    public function findByUniverseAndTick(string $universeId, int $tick): array;

    /**
     * @return ChronicleEvent[]
     */
    public function findByType(string $universeId, EventType $type, int $limit = 50): array;

    public function countByUniverse(string $universeId): int;
}
