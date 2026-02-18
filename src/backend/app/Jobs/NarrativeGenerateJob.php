<?php

namespace App\Jobs;

use App\Domains\Narrative\Planning\StoryOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Phase 5: Run narrative generation (arc + first chapter) async. Result stored in cache for pickup.
 */
class NarrativeGenerateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $universeId,
        private readonly array $worldState,
        private readonly array $pressurePoints,
        private readonly ?string $arcId = null,
        private readonly string $resultCacheKeyPrefix = 'narrative_result:'
    ) {
    }

    public function handle(StoryOrchestrator $orchestrator): void
    {
        try {
            $result = $orchestrator->generateArcAndFirstChapter(
                $this->worldState,
                $this->pressurePoints,
                $this->arcId
            );
            $key = $this->resultCacheKeyPrefix . $this->universeId;
            Cache::put($key, $result, now()->addHours(1));
        } catch (\Throwable $e) {
            Log::error('NarrativeGenerateJob failed', [
                'universe_id' => $this->universeId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
