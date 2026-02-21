<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Projection;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Build narrative context from world state and selected event (for LLM).
 */
final class ContextBuilder
{
    public function build(WorldStateVector $state, ?WorldEventDTO $event, array $recentEvents = []): array
    {
        $ctx = [
            'world_state' => $state->getAll(),
            'recent_events_count' => count($recentEvents),
        ];
        if ($event !== null) {
            $ctx['focus_event'] = [
                'type' => $event->type,
                'impact' => $event->impact,
                'tick' => $event->tick,
                'state_before' => $event->stateBefore,
                'state_after' => $event->stateAfter,
            ];
        }
        return $ctx;
    }
}
