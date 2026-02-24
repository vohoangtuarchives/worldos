<?php

namespace App\Jobs;

use WorldOS\Legacy\Domain\Runtime\UniverseRuntimeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WorldOS v3: Thin wrapper around UniverseRuntimeService::advance().
 * Keeps concurrency lock at job layer; all evolution logic delegated to the canonical V3 pipeline.
 *
 * @deprecated-logic The previous 80-line inline evolution loop (arc detection, preset transitions,
 *                   mutations, faction survival, influence recording) has been replaced by delegation
 *                   to UniverseRuntimeService → EvolutionEngineInterface → WorldEvolutionKernel.
 */
class TickUniverseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const LOCK_SECONDS = 60;

    public function __construct(
        private readonly string $universeId,
        private readonly int $ticksToRun = 1
    ) {
    }

    public function handle(UniverseRuntimeService $runtime): void
    {
        $lockKey = 'cosmology:universe:' . $this->universeId;
        $lock = Cache::lock($lockKey, self::LOCK_SECONDS);

        if (!$lock->get()) {
            Log::info("TickUniverseJob: could not acquire lock for universe {$this->universeId}, skipping.");
            return;
        }

        try {
            Log::info("TickUniverseJob (V3): advancing universe {$this->universeId} by {$this->ticksToRun} tick(s).");
            $runtime->advance($this->universeId, $this->ticksToRun);
            Log::info("TickUniverseJob (V3): completed {$this->ticksToRun} tick(s) for universe {$this->universeId}.");
        } catch (\Throwable $e) {
            Log::error("TickUniverseJob (V3): failed for universe {$this->universeId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
