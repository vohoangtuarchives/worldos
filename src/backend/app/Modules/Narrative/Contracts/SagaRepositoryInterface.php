<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Contracts;

use App\Modules\Narrative\Entities\SagaEntity;
use App\Modules\Narrative\ValueObjects\SagaId;

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
