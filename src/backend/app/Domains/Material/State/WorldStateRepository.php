<?php

namespace App\Domains\Material\State;

use App\Models\World;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * WorldStateRepository - Persistence for WorldState
 * 
 * Implements:
 * - Event log (append-only)
 * - Snapshots (every 10 epochs)
 * - State reconstruction
 */
class WorldStateRepository
{
    /**
     * Save state event to log.
     */
    public function saveEvent(
        string $worldId,
        int $epoch,
        array $deltas,
        array $origins,
        array $tickResults
    ): void {
        DB::table('world_state_events')->insert([
            'world_id' => $worldId,
            'epoch' => $epoch,
            'deltas' => json_encode($deltas),
            'origins' => json_encode($origins),
            'tick_results' => json_encode($tickResults),
            'created_at' => now(),
        ]);
    }

    /**
     * Save state snapshot.
     */
    public function saveSnapshot(WorldState $state): void
    {
        DB::table('world_state_snapshots')->insert([
            'id' => (string) Str::uuid(),
            'world_id' => $state->worldId,
            'epoch' => $state->epoch,
            'core_state' => json_encode($state->core->toArray()),
            'structural_state' => json_encode($state->structural->toArray()),
            'symbolic_state' => json_encode($state->symbolic->toArray()),
            'memory_state' => json_encode($state->memory->toArray()),
            'interaction_state' => json_encode($state->interaction->toArray()),
            'meta_state' => json_encode($state->meta->toArray()),
            'created_at' => now(),
        ]);
    }

    /**
     * Get latest snapshot for a world.
     */
    public function getLatestSnapshot(string $worldId): ?WorldState
    {
        $snapshot = DB::table('world_state_snapshots')
            ->where('world_id', $worldId)
            ->orderBy('epoch', 'desc')
            ->first();

        if (!$snapshot) {
            return null;
        }

        return WorldState::fromArray([
            'world_id' => $snapshot->world_id,
            'epoch' => $snapshot->epoch,
            'core_state' => json_decode($snapshot->core_state, true),
            'structural_state' => json_decode($snapshot->structural_state, true),
            'symbolic_state' => json_decode($snapshot->symbolic_state, true),
            'memory_state' => json_decode($snapshot->memory_state, true),
            'interaction_state' => json_decode($snapshot->interaction_state, true),
            'meta_state' => json_decode($snapshot->meta_state, true),
        ]);
    }

    /**
     * Get events since a specific epoch.
     */
    public function getEventsSince(string $worldId, int $sinceEpoch): array
    {
        return DB::table('world_state_events')
            ->where('world_id', $worldId)
            ->where('epoch', '>', $sinceEpoch)
            ->orderBy('epoch', 'asc')
            ->get()
            ->map(fn($event) => [
                'epoch' => $event->epoch,
                'deltas' => json_decode($event->deltas, true),
                'origins' => json_decode($event->origins, true),
                'tick_results' => json_decode($event->tick_results, true),
            ])
            ->toArray();
    }

    /**
     * Reconstruct state from snapshot + events.
     */
    public function reconstructState(string $worldId, int $targetEpoch): WorldState
    {
        // Get latest snapshot before target epoch
        $snapshot = DB::table('world_state_snapshots')
            ->where('world_id', $worldId)
            ->where('epoch', '<=', $targetEpoch)
            ->orderBy('epoch', 'desc')
            ->first();

        if (!$snapshot) {
            // No snapshot, start from initial state
            $state = WorldState::createInitial($worldId);
            $fromEpoch = 0;
        } else {
            $state = WorldState::fromArray([
                'world_id' => $snapshot->world_id,
                'epoch' => $snapshot->epoch,
                'core_state' => json_decode($snapshot->core_state, true),
                'structural_state' => json_decode($snapshot->structural_state, true),
                'symbolic_state' => json_decode($snapshot->symbolic_state, true),
                'memory_state' => json_decode($snapshot->memory_state, true),
                'interaction_state' => json_decode($snapshot->interaction_state, true),
                'meta_state' => json_decode($snapshot->meta_state, true),
            ]);
            $fromEpoch = $snapshot->epoch;
        }

        // Apply events from snapshot to target
        $events = $this->getEventsSince($worldId, $fromEpoch);
        $mutator = new WorldStateMutator();

        foreach ($events as $event) {
            if ($event['epoch'] > $targetEpoch) {
                break;
            }

            $state = $mutator->applyDeltas($state, $event['deltas'], $event['origins']);
        }

        return $state;
    }

    /**
     * Get current state for a world.
     */
    public function getCurrentState(string $worldId): WorldState
    {
        $latestEvent = DB::table('world_state_events')
            ->where('world_id', $worldId)
            ->orderBy('epoch', 'desc')
            ->first();

        if (!$latestEvent) {
            return WorldState::createInitial($worldId);
        }

        return $this->reconstructState($worldId, $latestEvent->epoch);
    }

    /**
     * Check if snapshot should be created (every 10 epochs).
     */
    public function shouldCreateSnapshot(int $epoch): bool
    {
        return $epoch % 10 === 0;
    }
}
