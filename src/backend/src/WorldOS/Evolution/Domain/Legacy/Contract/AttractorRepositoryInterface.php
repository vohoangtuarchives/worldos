<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Contract;

use WorldOS\Evolution\Domain\Legacy\Entity\AttractorAggregate;
use WorldOS\Evolution\Domain\Legacy\ValueObject\AttractorIncarnation;

interface AttractorRepositoryInterface
{
    /**
     * Find an attractor by its code (e.g., "EQUILIBRIUM", "CHAOS").
     */
    public function findByCode(string $code): ?AttractorAggregate;

    /**
     * Find an attractor by its ID.
     */
    public function findById(string $id): ?AttractorAggregate;

    /**
     * Save or update an attractor aggregate.
     */
    public function save(AttractorAggregate $attractor): void;

    /**
     * Save a new incarnation for an attractor.
     */
    public function saveIncarnation(AttractorIncarnation $incarnation): void;

    /**
     * Get the current active incarnation for an attractor.
     */
    public function getCurrentIncarnation(string $attractorId): ?AttractorIncarnation;

    /**
     * Get all incarnations for an attractor (full tree).
     */
    public function getIncarnationTree(string $attractorId): array;

    /**
     * Close (set end_tick) for an incarnation.
     */
    public function closeIncarnation(string $incarnationId, int $endTick): void;
}


