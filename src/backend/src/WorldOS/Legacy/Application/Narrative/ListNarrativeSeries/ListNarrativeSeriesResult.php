<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\ListNarrativeSeries;

final readonly class ListNarrativeSeriesResult
{
    /** @param list<array{id: string, title: string}> $narrativeSeries */
    public function __construct(
        public array $narrativeSeries,
    ) {
    }
}
