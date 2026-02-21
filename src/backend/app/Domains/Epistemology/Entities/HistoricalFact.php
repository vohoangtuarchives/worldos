<?php

namespace App\Domains\Epistemology\Entities;

/**
 * HistoricalFact is an immutable record within the CanonicalArchive.
 */
class HistoricalFact
{
    public function __construct(
        public readonly string $id,
        public readonly int $year,
        public readonly string $description,
        public readonly array $actors = [],
        public readonly float $impactScore = 0.0
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'description' => $this->description,
            'actors' => $this->actors,
            'impact_score' => $this->impactScore,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['year'],
            $data['description'],
            $data['actors'] ?? [],
            $data['impact_score'] ?? 0.0
        );
    }
}
