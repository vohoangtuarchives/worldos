<?php

namespace WorldOS\Legacy\Application\Narrative\Timeline;

use WorldOS\Saga\Domain\Narrative\ValueObject\StateSnapshot;
use Illuminate\Support\Str;

class TimelineNode
{
    public function __construct(
        public readonly string $id,
        public readonly array $parentIds,
        public readonly string $canonicalLevel, // MAIN, ALTERNATE
        public readonly StateSnapshot $snapshot
    ) {}

    public function fork(string $newLevel = 'ALTERNATE'): self
    {
        // Forking creates a new node with THIS node as parent
        // It inherits the snapshot initially
        return new self(
            (string) Str::uuid(),
            [$this->id],
            $newLevel,
            $this->snapshot // Copy snapshot
        );
    }

    public function isMain(): bool
    {
        return $this->canonicalLevel === 'MAIN';
    }
}
