<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\World\ContinuousWorldService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ContinuousWorldTickJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 10, 15]; // seconds
    public int $timeout = 60; // seconds

    public function __construct(
        private readonly string $worldId
    ) {}

    public function handle(ContinuousWorldService $continuousService): void
    {
        try {
            $success = $continuousService->executeContinuousTick($this->worldId);
            
            if (!$success) {
                Log::warning("Continuous tick job failed for world {$this->worldId}");
                
                // Don't retry immediately - let the system handle it
                $this->release(10);
                return;
            }

            Log::debug("Continuous tick job completed for world {$this->worldId}");

        } catch (\Exception $e) {
            Log::error("Continuous tick job error for world {$this->worldId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e; // Let the queue system handle retries
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Continuous tick job permanently failed for world {$this->worldId}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
        
        // Could trigger alert or notification here
    }
}
