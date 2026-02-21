<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Projection;

use Illuminate\Support\Facades\DB;

final class NarrativeProjectionRepository
{
    public function store(string $text, ?string $universeId = null, int $tick = 0, ?string $eventType = null, ?string $eventId = null, ?array $structuredSummary = null): void
    {
        DB::table('narrative_projections')->insert([
            'universe_id' => $universeId,
            'tick' => $tick,
            'event_type' => $eventType,
            'event_id' => $eventId,
            'text' => $text,
            'structured_summary' => $structuredSummary !== null ? json_encode($structuredSummary, JSON_THROW_ON_ERROR) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
