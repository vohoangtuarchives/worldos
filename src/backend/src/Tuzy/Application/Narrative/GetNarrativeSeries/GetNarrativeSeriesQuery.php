<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\GetNarrativeSeries;

final readonly class GetNarrativeSeriesQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
