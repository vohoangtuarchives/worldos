<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\UpdateNarrativeSeries;

final readonly class UpdateNarrativeSeriesCommand
{
    public function __construct(
        public string $id,
        public string $title,
    ) {
    }
}
