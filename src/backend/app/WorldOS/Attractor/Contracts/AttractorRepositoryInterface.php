<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\Contracts;

use App\WorldOS\Attractor\Entities\AttractorEntity;
use App\WorldOS\Attractor\ValueObjects\AttractorId;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Attractor Repository Contract.
 */
interface AttractorRepositoryInterface
{
    public function findById(AttractorId $id): ?AttractorEntity;

    public function save(AttractorEntity $attractor): void;

    /**
     * Find all attractors for a given Universe.
     *
     * @return AttractorEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array;

    /**
     * Find active attractors for a given Universe.
     *
     * @return AttractorEntity[]
     */
    public function findActiveByUniverseId(UniverseId $universeId): array;
}
