<?php

namespace WorldOS\Legacy\Application\WorldEvolution\Services;

use WorldOS\Legacy\Application\WorldEvolution\Entities\WorldState;
use WorldOS\Blueprint\Domain\LegacyEvolution\Events\WorldEvent;
use Illuminate\Support\Facades\Event;

/**
 * The CascadeEngine replaces the traditional "tick" system.
 * It resolves events and triggers chain reactions until equilibrium or collapse is reached.
 */
class CascadeEngine
{
    private const MAX_CASCADE_DEPTH = 5;
    private const COLLAPSE_THRESHOLD = 0.95; // e.g., Entropy > 0.95 triggers collapse

    public function processEvent(WorldState $state, WorldEvent $initialEvent): array
    {
        $eventChain = [];
        $queue = [$initialEvent];
        $depth = 0;

        while (!empty($queue) && $depth < self::MAX_CASCADE_DEPTH) {
            $currentEvent = array_shift($queue);
            $eventChain[] = $currentEvent;

            // Apply impact to world state
            $impacts = $currentEvent->getImpactVector();
            $state->applyVectorImpact($impacts);

            // Epistemic Ripple logic (increases instability per severe event)
            $state->applyEpistemicDrift($currentEvent->severity * 0.05, -($currentEvent->severity * 0.02));

            // Dispatch Laravel event for other listeners to react (e.g., Civilization Layer)
            // Listeners may return new WorldEvents to push into the cascade
            $reactions = Event::dispatch($currentEvent, [$state]);

            foreach ($reactions as $reaction) {
                if ($reaction instanceof WorldEvent) {
                    $queue[] = $reaction;
                }
            }

            // Check for critical thresholds (Collapse)
            if ($this->isCollapsed($state)) {
                $queue[] = new class($state->sagaId, $state->universeId, $state->currentYear) extends WorldEvent {
                    public function getName(): string { return "Tận Thế (Systemic Collapse)"; }
                    public function getImpactVector(): array {
                        return [ WorldStateVector::DIMENSION_ENTROPY => 1.0 ];
                    }
                };
                // Force end of cascade if collapse is reached to prevent infinite doom looping
                $depth = self::MAX_CASCADE_DEPTH; 
            }

            $depth++;
        }

        return $eventChain;
    }

    private function isCollapsed(WorldState $state): bool
    {
        // Simple heuristic: If entropy is critically high and order is zero
        // In real logic, this ties to Archetype limits.
        return $state->vector->getEntropy() >= self::COLLAPSE_THRESHOLD;
    }
}
