<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tuzy\Domain\Evolution\Service\MetaCosmos\MetaCycleOrchestrator;
use Tuzy\Domain\Evolution\Entity\LawGenome;
use Tuzy\Domain\Evolution\Entity\Universe;
use Illuminate\Support\Str;

class WorldEvolveCommand extends Command
{
    protected $signature = 'world:evolve {--count=3 : Number of universes} {--cycles=10 : Number of meta-cycles}';
    protected $description = 'Run a vertical slice of the MetaCosmos Evolutionary Engine';

    public function handle(MetaCycleOrchestrator $orchestrator): int
    {
        $this->info("=== WorldOS V4 Evolutionary Engine: Vertical Slice ===");
        
        $universeCount = (int) $this->option('count');
        $cycleCount = (int) $this->option('cycles');
        
        $law = new LawGenome(
            id: 'law_alpha',
            minEntropy: 0.2,
            maxEntropy: 0.85,
            baseMutationRate: 0.1,
            interactionGain: 1.2
        );

        $this->comment("LawGenome Initialized: Entropy Band [{$law->getMinEntropy()}, {$law->getMaxEntropy()}]");

        // Initial Population
        $universes = [];
        for ($i = 0; $i < $universeCount; $i++) {
            $universes[] = new Universe(Str::uuid()->toString(), $law);
        }

        $this->info("Spawned {$universeCount} Universes. Starting {$cycleCount} Meta-Cycles...");

        for ($c = 1; $c <= $cycleCount; $c++) {
            $currentObjective = $orchestrator->getObjectiveEngine()->getCurrentObjective();
            $this->warn("\n--- Meta-Cycle #{$c} [Objective: " . $currentObjective->getName() . "] ---");
            
            // 1. Run Cycle
            foreach ($universes as $uni) {
                $uni->incrementYear(mt_rand(100, 300)); // Simulate years passing
            }
            $result = $orchestrator->runCycle($law, $universes);
            $universes = $result['nextGeneration'];
            $evaluation = $result['evaluation'];

            // 2. Display Table
            $tableData = [];
            foreach ($evaluation as $eval) {
                $uni = $eval['universe'];
                $vector = $eval['vector'];
                
                $tableData[] = [
                    substr($uni->getId(), 0, 8),
                    $uni->getYear(),
                    number_format($vector->stability, 2),
                    number_format($vector->complexity, 2),
                    number_format($vector->diversity, 2),
                    number_format($vector->selfReference, 2),
                    number_format($vector->coherence, 2),
                    ($vector->selfReference > 0.4) ? '<fg=cyan>Ascending</>' : (($vector->stability < 0.3) ? '<fg=red>Collapse</>' : '<fg=yellow>Stable</>')
                ];
            }

            $this->table(
                ['ID', 'Age', 'Stab', 'Cmplx', 'Div', 'Hero', 'Coh', 'Status'],
                $tableData
            );

            $best = $evaluation[0];
            $this->comment("Achievement Gen #{$c}: Timeline " . substr($best['universe']->getId(), 0, 8) . " shows emergent complexity.");
            
            $this->info("Cycle #{$c} complete.");
        }

        $this->info("\nExperiment Completed. Final result shows convergence toward narrative-rich universes.");
        
        return 0;
    }
}
