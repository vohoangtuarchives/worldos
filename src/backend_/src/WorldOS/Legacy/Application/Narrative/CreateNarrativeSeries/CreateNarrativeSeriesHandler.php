<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\CreateNarrativeSeries;

use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;
use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class CreateNarrativeSeriesHandler
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $repository,
    ) {
    }

    public function handle(CreateNarrativeSeriesCommand $command): CreateNarrativeSeriesResult
    {
        $series = NarrativeSeries::create($command->title);
        $this->repository->save($series);
        return new CreateNarrativeSeriesResult($series->getId(), $series->getTitle());
    }
}
