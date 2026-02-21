<?php

namespace Tuzy\Application\Epistemology\Entities;

/**
 * CanonicalArchive stores the absolute, unchanging timeline of events.
 * It is append-only and represents what ACTUALLY happened.
 */
class CanonicalArchive
{
    /** @var array<string, HistoricalFact> */
    private array $facts = [];

    public function __construct(array $facts = [])
    {
        foreach ($facts as $fact) {
            $this->addFact($fact);
        }
    }

    public function addFact(HistoricalFact $fact): void
    {
        $this->facts[$fact->id] = $fact;
    }

    public function fetchAll(): array
    {
        return $this->facts;
    }

    public function fetchByYearRange(int $startYear, int $endYear): array
    {
        return array_filter($this->facts, fn(HistoricalFact $f) => $f->year >= $startYear && $f->year <= $endYear);
    }

    public function toArray(): array
    {
        return array_map(fn($f) => $f->toArray(), array_values($this->facts));
    }

    public static function fromArray(array $data): self
    {
        $facts = array_map(fn($f) => HistoricalFact::fromArray($f), $data);
        return new self($facts);
    }
}
