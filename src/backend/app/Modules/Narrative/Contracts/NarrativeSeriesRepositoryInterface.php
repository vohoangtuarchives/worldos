<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Contracts;

use App\Modules\Narrative\Entities\NarrativeSeriesEntity;
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
