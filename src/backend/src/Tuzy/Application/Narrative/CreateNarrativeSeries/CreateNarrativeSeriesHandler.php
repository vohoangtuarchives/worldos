<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\CreateNarrativeSeries;

use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

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
