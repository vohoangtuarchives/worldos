<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Jobs;

use App\Modules\Narrative\Contracts\SagaRepositoryInterface;
use App\Modules\Narrative\ValueObjects\SagaId;
use App\Modules\Universe\Jobs\TickUniverseJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AdvanceSagaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public readonly string $sagaId,
        public readonly int $ticks = 1,
    ) {
    }

    public function handle(SagaRepositoryInterface $sagaRepository): void
    {
        Log::info("Processing AdvanceSagaJob for Saga [{$this->sagaId}] for {$this->ticks} ticks.");

        $id = SagaId::fromString($this->sagaId);
        $saga = $sagaRepository->findById($id);

        if ($saga === null) {
            Log::error("Saga [{$this->sagaId}] not found. Aborting job.");
            return;
        }

        if (!$saga->canAdvance()) {
            Log::warning("Saga [{$this->sagaId}] cannot advance: status [{$saga->getStatus()->value}]. Aborting.");
            return;
        }

        // We queue up the VERY FIRST tick for all universes in this saga 
        // to bootstrap the cycle. The Redis PubSub listener will handle subsequent ticks
        // until target_tick is met.
        foreach ($saga->getUniverseIds() as $universeId) {
            TickUniverseJob::dispatch($universeId);
        }

        Log::info("Bootstrapped first wave of TickUniverseJobs for Saga [{$this->sagaId}].");
    }
}
