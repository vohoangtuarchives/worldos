<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\Repository;

use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;

interface NarrativeSeriesRepositoryInterface
{
    /** @return list<NarrativeSeries> */
    public function findAll(): array;

    public function findById(string $id): ?NarrativeSeries;

    public function save(NarrativeSeries $series): void;
}
