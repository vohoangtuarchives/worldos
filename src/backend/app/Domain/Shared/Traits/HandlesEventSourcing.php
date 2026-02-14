<?php

namespace App\Domain\Shared\Traits;

use App\StoryEngine\Persistence\EventStore;
use App\StoryEngine\WorldState;
use App\Exceptions\Simulation\SimulationException;

trait HandlesEventSourcing
{
    /**
     * @var EventStore|null
     */
    protected ?EventStore $eventStore = null;

    /**
     * @var string
     */
    protected string $timelineId = 'default';

    /**
     * Initialize event sourcing.
     */
    protected function initializeEventSourcing(string $timelineId = 'default'): void
    {
        $this->timelineId = $timelineId;
        $this->eventStore = new EventStore();
    }

    /**
     * Store an event in the event store.
     */
    protected function storeEvent(string $eventType, array $data, int $tick = null): void
    {
        if (!$this->eventStore) {
            throw SimulationException::replayError($this->timelineId, 'Event store not initialized');
        }

        $this->eventStore->store($this->timelineId, $eventType, $data, $tick);
    }

    /**
     * Replay events to reconstruct world state.
     */
    protected function replayEvents(string $timelineId, int $targetTick = null): WorldState
    {
        if (!$this->eventStore) {
            $this->initializeEventSourcing($timelineId);
        }

        $worldState = new WorldState();
        $events = $this->eventStore->getEvents($timelineId, $targetTick);

        foreach ($events as $event) {
            $this->applyEventToWorldState($event, $worldState);
        }

        return $worldState;
    }

    /**
     * Apply a single event to world state.
     */
    protected function applyEventToWorldState(array $event, WorldState $worldState): void
    {
        switch ($event['event_type']) {
            case 'world_state_update':
                $this->applyWorldStateUpdate($event['data'], $worldState);
                break;

            case 'faction_action':
                $this->applyFactionAction($event['data'], $worldState);
                break;

            case 'seed_application':
                $this->applySeedApplication($event['data'], $worldState);
                break;

            case 'material_pressure':
                $this->applyMaterialPressure($event['data'], $worldState);
                break;

            default:
                // Log unknown event type but don't fail
                \Log::warning("Unknown event type: {$event['event_type']}", [
                    'event' => $event,
                    'timeline' => $this->timelineId
                ]);
                break;
        }
    }

    /**
     * Apply world state update event.
     */
    protected function applyWorldStateUpdate(array $data, WorldState $worldState): void
    {
        if (isset($data['public_awareness'])) {
            $worldState->publicAwareness = $data['public_awareness'];
        }

        if (isset($data['power_centers'])) {
            $worldState->powerCenters = $data['power_centers'];
        }

        if (isset($data['tier_index'])) {
            $worldState->tierIndex = $data['tier_index'];
        }
    }

    /**
     * Apply faction action event.
     */
    protected function applyFactionAction(array $data, WorldState $worldState): void
    {
        // Implementation depends on faction system structure
        // This is a placeholder for faction state updates
        \Log::info('Applying faction action', [
            'faction_id' => $data['faction_id'] ?? 'unknown',
            'action' => $data['action'] ?? 'unknown',
            'timeline' => $this->timelineId
        ]);
    }

    /**
     * Apply seed application event.
     */
    protected function applySeedApplication(array $data, WorldState $worldState): void
    {
        // Implementation depends on seed system structure
        \Log::info('Applying seed', [
            'seed_type' => $data['seed_type'] ?? 'unknown',
            'severity' => $data['severity'] ?? 0,
            'timeline' => $this->timelineId
        ]);
    }

    /**
     * Apply material pressure event.
     */
    protected function applyMaterialPressure(array $data, WorldState $worldState): void
    {
        // Implementation depends on material system structure
        \Log::info('Applying material pressure', [
            'source' => $data['source'] ?? 'unknown',
            'target' => $data['target'] ?? 'unknown',
            'pressure' => $data['pressure'] ?? 0,
            'timeline' => $this->timelineId
        ]);
    }

    /**
     * Create a snapshot of current world state.
     */
    protected function createSnapshot(WorldState $worldState, int $tick): array
    {
        return [
            'timeline_id' => $this->timelineId,
            'tick' => $tick,
            'world_state' => [
                'public_awareness' => $worldState->publicAwareness,
                'power_centers' => $worldState->powerCenters,
                'tier_index' => $worldState->tierIndex,
                'faction_count' => count($worldState->factions),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Validate event sequence integrity.
     */
    protected function validateEventSequence(array $events): bool
    {
        $expectedTick = 0;

        foreach ($events as $event) {
            if ($event['tick'] < $expectedTick) {
                throw SimulationException::replayError(
                    $this->timelineId,
                    "Event sequence violation: tick {$event['tick']} < expected {$expectedTick}"
                );
            }
            $expectedTick = $event['tick'] + 1;
        }

        return true;
    }

    /**
     * Get event count for timeline.
     */
    protected function getEventCount(string $timelineId = null): int
    {
        $timelineId = $timelineId ?? $this->timelineId;

        if (!$this->eventStore) {
            return 0;
        }

        return $this->eventStore->getEventCount($timelineId);
    }

    /**
     * Clear events for timeline (use with caution).
     */
    protected function clearTimeline(string $timelineId = null): void
    {
        $timelineId = $timelineId ?? $this->timelineId;

        if (!$this->eventStore) {
            return;
        }

        $this->eventStore->clearTimeline($timelineId);
    }

    /**
     * Fork timeline from a specific tick.
     */
    protected function forkTimeline(string $newTimelineId, int $fromTick = null): string
    {
        if (!$this->eventStore) {
            throw SimulationException::timelineForkFailed(
                $newTimelineId,
                'Event store not initialized'
            );
        }

        return $this->eventStore->forkTimeline($this->timelineId, $newTimelineId, $fromTick);
    }
}
