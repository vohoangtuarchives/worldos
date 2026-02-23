<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\UpdateNarrativeSeries;

use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;
use WorldOS\Saga\Domain\Narrative\Exception\NarrativeSeriesNotFoundException;
use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class UpdateNarrativeSeriesHandler
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateNarrativeSeriesCommand $command): void
    {
        $existing = $this->repository->findById($command->id);
        if ($existing === null) {
            throw NarrativeSeriesNotFoundException::withId($command->id);
        }
        $series = NarrativeSeries::create($command->title, $command->id);
        $this->repository->save($series);
    }
}
