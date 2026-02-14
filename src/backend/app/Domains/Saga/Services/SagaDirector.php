<?php

namespace App\Domains\Saga\Services;

use App\Models\World;
use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaWorld;
use App\Domains\Saga\Services\EntropyPressureService;
use App\Domains\Saga\Actions\TerraformWorldAction;
use Illuminate\Support\Facades\Log;

class SagaDirector
{
    public function __construct(
        protected EntropyPressureService $pressureService,
        protected TerraformWorldAction $terraformAction
    ) {}

    /**
     * Evaluate the entire saga for potential cross-world events.
     */
    public function evaluateSaga(Saga $saga): void
    {
        // Get all active worlds in this saga
        $sagaWorlds = $saga->sagaWorlds()
            ->with('world')
            ->whereHas('world', function($query) {
                $query->where('status', 'active');
            })
            ->get();


        if ($sagaWorlds->count() < 2) {
            return;
        }

        foreach ($sagaWorlds as $sourceSagaWorld) {
            $source = $sourceSagaWorld->world;
            
            foreach ($sagaWorlds as $targetSagaWorld) {
                if ($source->id === $targetSagaWorld->world->id) {
                    continue;
                }

                $target = $targetSagaWorld->world;
                $this->evaluateInteraction($source, $target);
            }
        }
    }

    /**
     * Evaluate interaction between two specific worlds.
     */
    protected function evaluateInteraction(World $source, World $target): void
    {
        // 1. Calculate Pressure
        $pressure = $this->pressureService->calculatePressure($source, $target);

        // 2. Threshold Logic
        // If pressure is extreme (>= 0.7), trigger a Terraform Attempt (Invasion)
        if ($pressure >= 0.7) {
            Log::info("SagaDirector: Extreme pressure detected. Triggering Terraform Attempt.", [
                'source' => $source->name,
                'target' => $target->name,
                'pressure' => $pressure
            ]);
            
            try {
                $this->terraformAction->handle($source, $target);
            } catch (\Exception $e) {
                Log::error("SagaDirector: Terraform Action failed: " . $e->getMessage());
            }
            return;
        }

        // If pressure is medium (>= 0.3), ensure a rift exists but don't force reality rewrite yet
        if ($pressure >= 0.3) {
            // This is handled by EntropyPressureService::getOrCreateGate in a real scenario
            // or we could explicitly open/close gates here.
            Log::debug("SagaDirector: High pressure. Rift is stable.", [
                'source' => $source->name,
                'target' => $target->name,
                'pressure' => $pressure
            ]);
        }
    }

    /**
     * Determine the target duration for a world in the saga.
     * 
     * @param SagaWorld $sagaWorld
     * @return int Duration in years
     */
    public function determineWorldDuration(SagaWorld $sagaWorld): int
    {
        // Logic to determine duration based on saga pacing, genre, or world type.
        // For now, default to 1000 years or read from config if present.
        return 1000;
    }
}
