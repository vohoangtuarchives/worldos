<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\ListNarrativeSeries;

final readonly class ListNarrativeSeriesResult
{
    /** @param list<array{id: string, title: string}> $narrativeSeries */
    public function __construct(
        public array $narrativeSeries,
    ) {
    }
}
