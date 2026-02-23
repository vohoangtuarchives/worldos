<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Legacy\Repository;

use WorldOS\Saga\Domain\Legacy\Entity\Saga;

interface SagaRepositoryInterface
{
    /** @return list<Saga> */
    public function findAll(): array;

    public function findById(string $id): ?Saga;

    public function save(Saga $saga): void;
}
