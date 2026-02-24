<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\CreateNarrativeSeries;

final class CreateNarrativeSeriesCommand
{
    public function __construct(
        public readonly string $title,
    ) {
    }
}
