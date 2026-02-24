<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Contracts;

use App\WorldOS\Narrative\Entities\NarrativeSeriesEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

interface NarrativeSeriesRepositoryInterface
{
    public function findById(string $id): ?NarrativeSeriesEntity;

    public function save(NarrativeSeriesEntity $series): void;

    /**
     * @return NarrativeSeriesEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array;

    public function findActiveByUniverseId(UniverseId $universeId): ?NarrativeSeriesEntity;
}
