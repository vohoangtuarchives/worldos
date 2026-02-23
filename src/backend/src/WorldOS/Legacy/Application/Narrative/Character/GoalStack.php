<?php

namespace WorldOS\Legacy\Application\Narrative\Character;

use Illuminate\Support\Collection;

class GoalStack
{
    protected Collection $items;

    public function __construct(array $goals = [])
    {
        $this->items = collect($goals); // Array of Goal objects/arrays
    }

    public function add(array $goal): void
    {
        $this->items->push($goal); // { description, priority, status }
    }

    public function getHighestPriority(): ?array
    {
        return $this->items
            ->where('status', 'active')
            ->sortByDesc('priority')
            ->first();
    }
    
    public function all(): array
    {
        return $this->items->all();
    }
}
