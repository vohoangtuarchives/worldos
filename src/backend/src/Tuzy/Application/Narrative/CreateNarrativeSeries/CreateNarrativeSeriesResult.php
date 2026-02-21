<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\CreateNarrativeSeries;

final class CreateNarrativeSeriesResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
    ) {
    }
}
