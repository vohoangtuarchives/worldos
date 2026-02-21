<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\Repository;

use Tuzy\Domain\Saga\Entity\Saga;

interface SagaRepositoryInterface
{
    /** @return list<Saga> */
    public function findAll(): array;

    public function findById(string $id): ?Saga;

    public function save(Saga $saga): void;
}
