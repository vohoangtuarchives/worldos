<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Contracts;

use App\WorldOS\Saga\Entities\SagaEntity;
use App\WorldOS\Saga\ValueObjects\SagaId;

/**
 * Saga Repository Contract — domain-layer data access.
 */
interface SagaRepositoryInterface
{
    public function findById(SagaId $id): ?SagaEntity;

    public function save(SagaEntity $saga): void;

    /**
     * @return SagaEntity[]
     */
    public function findActive(): array;
}
