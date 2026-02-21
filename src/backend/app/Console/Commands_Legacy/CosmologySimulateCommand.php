<?php

namespace App\Console\Commands;

use App\Domains\Cosmology\Cosmology;
use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Services\LLMChronicler;
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

        // Setup Narrative Engine (uses LLM when configured, else rich template)
        $chronicler = app(LLMChronicler::class);

        $this->info("Simulation Start. Universe: " . $universe->getId());
        $this->renderState($universe);

        for ($i = 0; $i < $steps; $i++) {
            $this->info("--- Tick " . ($i + 1) . " ---");
            
            // Evolve
            $cosmology->tick();
            
            // Render Math State
            $this->renderState($universe);
            
            // Render Narrative
            $chronicle = $chronicler->chronicle($universe);
            $this->line("<comment>Narrative:</comment> " . $chronicle);
            
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

