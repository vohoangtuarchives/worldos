<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\Repository;

use Tuzy\Domain\Narrative\Entity\NarrativeSeries;

interface NarrativeSeriesRepositoryInterface
{
    /** @return list<NarrativeSeries> */
    public function findAll(): array;

    public function findById(string $id): ?NarrativeSeries;

    public function save(NarrativeSeries $series): void;
}
