<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\GetNarrativeSeries;

use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;
use WorldOS\Saga\Domain\Narrative\Exception\NarrativeSeriesNotFoundException;
use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class GetNarrativeSeriesHandler
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $repository,
    ) {
    }

    public function handle(GetNarrativeSeriesQuery $query): NarrativeSeries
    {
        $series = $this->repository->findById($query->id);
        if ($series === null) {
            throw NarrativeSeriesNotFoundException::withId($query->id);
        }
        return $series;
    }
}
