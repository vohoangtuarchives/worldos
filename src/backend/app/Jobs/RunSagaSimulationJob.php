<?php

namespace App\Jobs;

use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated WorldOS v3: Writer Genesis uses SagaService::genesisV3; new sagas should not dispatch this job. Kept for legacy/fallback.
 */
class RunSagaSimulationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Saga $saga
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SagaRunner $sagaRunner): void
    {
        Log::info("Starting Saga Simulation Job for Saga ID: {$this->saga->id}");

        try {
            $sagaRunner->runSync($this->saga);
            Log::info("Saga Simulation Job completed for Saga ID: {$this->saga->id}");
        } catch (\Exception $e) {
            Log::error("Saga Simulation Job failed for Saga ID: {$this->saga->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Optionally fail the saga model if needed, but SagaRunner might handle it.
            if ($this->saga->refresh()->status !== 'failed') {
                $this->saga->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }
}
