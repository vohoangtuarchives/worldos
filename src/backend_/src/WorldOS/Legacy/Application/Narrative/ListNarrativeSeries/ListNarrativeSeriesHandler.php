<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\ListNarrativeSeries;

use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class ListNarrativeSeriesHandler
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $narrativeSeriesRepository,
    ) {
    }

    public function handle(ListNarrativeSeriesQuery $query): ListNarrativeSeriesResult
    {
        $entities = $this->narrativeSeriesRepository->findAll();
        $items = [];
        foreach ($entities as $entity) {
            $items[] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
            ];
        }
        return new ListNarrativeSeriesResult($items);
    }
}
