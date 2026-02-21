<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\Event;

final class NarrativeSeriesCreated
{
    public function __construct(
        public readonly string $seriesId,
        public readonly string $title,
    ) {
    }
}
