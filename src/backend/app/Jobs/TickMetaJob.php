<?php

namespace App\Jobs;

use WorldOS\Legacy\Domain\Meta\Aggregates\MetaLayer;
use App\Models\MetaLayerState; // Need to create this model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class TickMetaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $targetTick
    ) {}

    public function handle(): void
    {
        $lockKey = "lock:meta:tick";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 30);

        if (!$lock->get()) {
            return;
        }

        try {
            // 1. Load State (Singleton)
            $stateModel = MetaLayerState::instance();
            
            // 2. Rehydrate Aggregate
            $metaLayer = new MetaLayer();
            $metaLayer->hydrate($stateModel->toArray());

            // 3. Evolve
            $metaLayer->evolve();

            // 4. Save State
            $stateModel->update($metaLayer->exportState());

            // 5. Check Extinction
            $severity = $metaLayer->checkExtinction();
            if ($severity) {
                // Create Extinction Impulse
                \App\Models\MetaImpulse::create([
                    'type' => 'MASS_EXTINCTION',
                    'payload' => ['severity' => $severity],
                    'strength' => $severity,
                    'decay_rate' => 0.05,
                    'created_at_tick' => $this->targetTick,
                    'active_until_tick' => $this->targetTick + 20, // Lasts 20 ticks
                ]);
                
                Log::warning("Mass Extinction triggered at tick {$this->targetTick} with severity {$severity}");
            }

            // 6. Capture Snapshot
            $runId = '00000000-0000-0000-0000-000000000000';
            $snapshotService = app(\WorldOS\Legacy\Application\Replay\Services\SnapshotService::class);
            $snapshotService->captureMeta($metaLayer, $runId, $this->targetTick);

        } catch (\Exception $e) {
            Log::error("Meta Tick failed: {$e->getMessage()}");
        } finally {
            $lock->release();
        }
    }
}
