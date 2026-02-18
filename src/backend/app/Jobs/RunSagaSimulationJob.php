<?php

namespace App\Jobs;

use App\Domains\Saga\Saga;
use App\Domains\Saga\Services\SagaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * WorldOS v3: Runs Saga simulation via SagaService (Universe-centric pipeline).
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
        public Saga $saga,
        public int $ticksPerUniverse = 10
    ) {}

    /**
     * Execute the job — V3: SagaService replaces SagaRunner.
     */
    public function handle(SagaService $sagaService): void
    {
        Log::info("Starting Saga Simulation Job (V3) for Saga ID: {$this->saga->id}");

        try {
            $sagaService->runBatchWithEvaluation($this->saga, $this->ticksPerUniverse);
            Log::info("Saga Simulation Job (V3) completed for Saga ID: {$this->saga->id}");
        } catch (\Exception $e) {
            Log::error("Saga Simulation Job (V3) failed for Saga ID: {$this->saga->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->saga->refresh()->status !== 'failed') {
                $this->saga->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }
}
