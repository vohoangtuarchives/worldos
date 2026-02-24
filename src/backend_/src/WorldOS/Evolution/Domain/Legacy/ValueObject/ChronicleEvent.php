<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * ChronicleEvent - Represents a significant historical event extracted from the evolution pipeline.
 */
final class ChronicleEvent
{
    public const SEVERITY_LOW = 'LOW';
    public const SEVERITY_MEDIUM = 'MEDIUM';
    public const SEVERITY_HIGH = 'HIGH';
    public const SEVERITY_CRITICAL = 'CRITICAL';

    public function __construct(
        public readonly int $year,
        public readonly string $type,
        public readonly string $title,
        public readonly string $description,
        public readonly string $severity = self::SEVERITY_MEDIUM,
        public readonly array $metadata = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'metadata' => $this->metadata,
        ];
    }
}
