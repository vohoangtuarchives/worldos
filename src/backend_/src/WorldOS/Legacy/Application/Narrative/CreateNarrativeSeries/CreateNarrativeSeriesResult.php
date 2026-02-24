<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\CreateNarrativeSeries;

final class CreateNarrativeSeriesResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
    ) {
    }
}
