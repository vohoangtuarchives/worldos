<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\GetNarrativeSeries;

use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Exception\NarrativeSeriesNotFoundException;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

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
