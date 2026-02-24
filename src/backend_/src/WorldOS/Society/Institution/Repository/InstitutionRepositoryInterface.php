<?php

declare(strict_types=1);

namespace WorldOS\Society\Institution\Repository;

use WorldOS\Society\Institution\Entity\Institution;

/**
 * Port: persistence for Institution aggregate.
 * Domain-only; implementation in Infrastructure.
 */
interface InstitutionRepositoryInterface
{
    /** @return list<Institution> */
    public function findAllForWorld(string $worldId): array;

    public function findById(string $id): ?Institution;

    public function save(Institution $institution): void;
}
