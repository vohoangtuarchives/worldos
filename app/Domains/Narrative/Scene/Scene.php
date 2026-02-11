<?php

namespace App\Domains\Narrative\Scene;

use Illuminate\Support\Collection;

/**
 * Represents the current context of the conversation.
 * In a full system, this would point to a TimelineNode.
 * For now, it holds active agents and goals.
 */
class Scene
{
    public function __construct(
        public readonly string $id,
        public readonly string $goal, // e.g. "A must find out B's secret"
        public readonly Collection $activeAgents, // Collection<Character>
        public array $state = [] // Arbitrary scene state (e.g., tension level)
    ) {}

    public function isResolved(): bool
    {
        // Placeholder logic
        return ($this->state['resolved'] ?? false) === true;
    }
}
