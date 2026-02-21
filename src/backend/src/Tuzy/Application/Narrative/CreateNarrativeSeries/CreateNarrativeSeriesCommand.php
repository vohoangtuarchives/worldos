<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\CreateNarrativeSeries;

final class CreateNarrativeSeriesCommand
{
    public function __construct(
        public readonly string $title,
    ) {
    }
}
