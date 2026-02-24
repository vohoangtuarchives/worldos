<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Chronicle;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5.1: Write event/state log entries to narrative_chronicle_events (worldOS → Chronicle).
 * Call from Cosmology tick/snapshot or Saga to feed the Narrative Renderer.
 */
class ChronicleEventWriter
{
    public function write(
        string $eventType,
        ?int $tick = null,
        ?string $stateRef = null,
        ?array $payload = null,
        string $sourceType = 'cosmology',
        ?string $sourceId = null
    ): void {
        if (!Schema::hasTable('narrative_chronicle_events')) {
            return;
        }

        DB::table('narrative_chronicle_events')->insert([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'event_type' => $eventType,
            'tick' => $tick,
            'state_ref' => $stateRef,
            'payload' => $payload !== null ? json_encode($payload) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
