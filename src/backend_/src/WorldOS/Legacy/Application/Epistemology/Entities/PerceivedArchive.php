<?php

namespace WorldOS\Legacy\Application\Epistemology\Entities;

/**
 * PerceivedArchive stores the timeline as it is currently understood by the civilizations.
 * Elements here can be retroactively mutated (Retroactive Ripple) due to Epistemic Instability.
 */
class PerceivedArchive
{
    /** @var array<string, HistoricalFact> */
    private array $perceivedFacts = [];

    public function __construct(array $facts = [])
    {
        foreach ($facts as $fact) {
            $this->addFact($fact);
        }
    }

    public function addFact(HistoricalFact $fact): void
    {
        // Initially, the perceived fact might match the canonical one
        $this->perceivedFacts[$fact->id] = $fact;
    }

    public function mutateFact(string $factId, HistoricalFact $mutatedFact): void
    {
        if (isset($this->perceivedFacts[$factId])) {
            $this->perceivedFacts[$factId] = $mutatedFact;
        }
    }

    /**
     * Retrieve the archive for AI prompt/planner to use.
     */
    public function fetchForPlanner(): array
    {
        return array_values($this->perceivedFacts);
    }

    public function toArray(): array
    {
        return array_map(fn($f) => $f->toArray(), array_values($this->perceivedFacts));
    }

    public static function fromArray(array $data): self
    {
        $facts = array_map(fn($f) => HistoricalFact::fromArray($f), $data);
        return new self($facts);
    }
}
