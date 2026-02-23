<?php

namespace WorldOS\Legacy\Application\Narrative\Character;

use WorldOS\Legacy\Application\Narrative\Character\Entities\Memory;
use Illuminate\Support\Collection;

class MemoryCollection
{
    /** @var Collection<Memory> */
    protected Collection $items;

    public function __construct(array $memories = [])
    {
        $this->items = collect($memories);
    }

    public function add(Memory $memory): void
    {
        $this->items->push($memory);
    }

    public function all(): array
    {
        return $this->items->all();
    }

    public function filterByVisibility(array $allowedVisibilities): self
    {
        $filtered = $this->items->filter(fn (Memory $m) => in_array($m->visibility, $allowedVisibilities));
        return new self($filtered->all());
    }
    
    // Future: relevantFor(Scene $scene)
}
