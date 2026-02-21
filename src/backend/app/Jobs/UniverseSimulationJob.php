<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Tuzy\Domain\Evolution\Service\WorldEvolutionPipeline;
use Tuzy\Domain\Evolution\Service\Fitness\UniverseFitnessEvaluator;
use Tuzy\Domain\Evolution\Entity\Universe;
use Tuzy\Domain\Evolution\Entity\LawGenome;

class UniverseSimulationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $universeId,
        private readonly int $yearsToSimulate = 100
    ) {}

    public function handle(
        WorldEvolutionPipeline $pipeline,
        UniverseFitnessEvaluator $fitnessEvaluator
    ): void {
        Log::info("UniverseSimulationJob: Starting simulation for Universe {$this->universeId}");

        // 1. Load Universe (Placeholder: In real app, load from Eloquent/Repository)
        // $universe = UniverseRepository->find($this->universeId);
        
        // Mocking for now to show the flow
        $law = new LawGenome('law_default');
        $universe = new Universe($this->universeId, $law);

        // 2. Simulation Loop
        for ($year = 0; $year < $this->yearsToSimulate; $year++) {
            // $pipeline->step($universe, ...);
            $universe->incrementYear(1);
        }

        // 3. Evaluate Fitness
        $civilizations = []; // TODO: Load actual civs from this universe
        $fitness = $fitnessEvaluator->evaluate($universe, $civilizations);

        Log::info("UniverseSimulationJob: Completed simulation for Universe {$this->universeId}. Fitness: {$fitness}");

        // 4. Update Universe Fitness & State
        // UniverseRepository->saveFitness($this->universeId, $fitness);
        
        // 5. Update MetaCycle progress
        // MetaCycleManager->markUniverseComplete($this->universeId, $fitness);
    }
}
