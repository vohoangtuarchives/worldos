<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\Event;

final class NarrativeSeriesCreated
{
    public function __construct(
        public readonly string $seriesId,
        public readonly string $title,
    ) {
    }
}
