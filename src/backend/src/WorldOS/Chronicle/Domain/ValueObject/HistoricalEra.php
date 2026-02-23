<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\ValueObject;

/**
 * HistoricalEra — A significant block of time in a Universe's history.
 * Groups events together under a single narrative theme.
 */
final readonly class HistoricalEra
{
    public function __construct(
        public string $name,
        public int    $startTick,
        public int    $endTick,
        public string $theme, // e.g., 'collapse', 'prosperity', 'chaos', 'renaissance'
        public string $description,
        public array  $metadata = []
    ) {
    }

    public function getDuration(): int
    {
        return $this->endTick - $this->startTick;
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'start_tick'  => $this->startTick,
            'end_tick'    => $this->endTick,
            'theme'       => $this->theme,
            'description' => $this->description,
            'metadata'    => $this->metadata,
        ];
    }
}
