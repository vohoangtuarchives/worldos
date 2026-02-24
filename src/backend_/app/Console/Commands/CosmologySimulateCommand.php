<?php

namespace App\Console\Commands;

use WorldOS\Legacy\Domain\Cosmology\Cosmology;
use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use Illuminate\Console\Command;

class CosmologySimulateCommand extends Command
{
    protected $signature = 'cosmology:simulate {steps=10}';
    protected $description = 'Run the Cosmological Runtime simulation loop';

    public function handle()
    {
        $steps = (int) $this->argument('steps');
        $this->info("Initializing Cosmological Runtime...");

        // Boot Cosmology
        $cosmology = Cosmology::boot();
        
        // Seed a Universe
        // Start with high order but some entropy
        $initialState = WorldStateVector::create(0.9, 0.1, 0.8, 0.9, 0.5, 0.2);
        $universe = new Universe($initialState, [], 'prime-universe');
        $cosmology->getFieldSpace()->addUniverse($universe);

        $this->info("Simulation Start. Universe: " . $universe->getId());
        $this->renderState($universe);

        for ($i = 0; $i < $steps; $i++) {
            $this->info("--- Tick " . ($i + 1) . " ---");
            
            // Evolve
            $cosmology->tick();
            
            // Render Math State
            $this->renderState($universe);
            
            // Sleep for effect?
            // usleep(500000); 
        }

        $this->info("Simulation Complete.");
    }

    private function renderState(Universe $universe)
    {
        $s = $universe->getState();
        $this->table(
            ['Order', 'Entropy', 'Cohesion', 'Legitimacy', 'Innovation', 'Military', 'Inequality', 'Trauma', 'Elite', 'Rsrc'],
            [[
                number_format($s->getOrder(), 3),
                number_format($s->getEntropy(), 3),
                number_format($s->getCohesion(), 3),
                number_format($s->getLegitimacy(), 3),
                number_format($s->getInnovation(), 3),
                number_format($s->getMilitary(), 3),
                number_format($s->getInequality(), 3),
                number_format($s->getTrauma(), 3),
                number_format($s->getEliteCohesion(), 3),
                number_format($s->getResourceStock(), 3),
            ]]
        );
    }
}
