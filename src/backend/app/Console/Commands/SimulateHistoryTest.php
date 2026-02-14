<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use App\Models\Faction;
use App\Models\Scar;
use App\Models\Institution;
use App\Domains\History\Services\EntropyCalculator;
use App\Domains\History\Services\ScarImpactService;
use App\Domains\Institution\Services\HealingService;
use App\Domains\Faction\Services\FactionAgent;
use App\Domains\Faction\ValueObjects\IdeologyVector;
use Illuminate\Support\Str;

class SimulateHistoryTest extends Command
{
    protected $signature = 'simulate:history-test';
    protected $description = 'Verify Phase 31 Deterministic History Engine';

    public function handle(
        EntropyCalculator $entropyCalc,
        HealingService $healingService,
        FactionAgent $factionAgent
    )
    {
        $this->info('Starting History Engine Simulation...');

        // 1. Setup World
        $world = World::create([
            'id' => Str::uuid(),
            'name' => 'Test World ' . now()->timestamp,
            'is_active' => true,
            'meta_data' => []
        ]);
        $this->info("World Created: {$world->id}");

        // 2. Setup Factions
        $faction = Faction::create([
            'id' => Str::uuid(),
            'world_id' => $world->id,
            'name' => 'Pacifist Monk Order',
            'type' => 'religious',
            'ideology_vector' => (new IdeologyVector(0.1, 0.9, 0.1, 0.9, 0.9))->toArray(), // Low Militarism
            'attributes' => ['resources' => 100],
            'state' => 'active'
        ]);
        $this->info("Faction Created: {$faction->name} (Militarism: {$faction->getIdeology()->militarism})");

        // 3. Create a Scar (The Great War)
        // This scar increases Militarism globally.
        $scar = Scar::create([
            'id' => Str::uuid(),
            'world_id' => $world->id,
            'origin_event_id' => Str::uuid(), // Dummy event
            'wound_type' => 'war_trauma',
            'pain_score' => 100.0,
            'belief_shift_vector' => ['militarism' => 0.2], // +0.2 Militarism pressure
            'decay_rate' => 0.01,
            'created_tick' => 100,
            'state' => 'active'
        ]);
        $this->info("Scar Created: The Great War (+0.2 Militarism pressure)");

        // 4. Check Entropy (Before Healing)
        // Tick 110 (10 ticks later)
        $currentTick = 110;
        $entropy = $entropyCalc->calculateWorldEntropy($world, $currentTick);
        $this->info("Entropy at Tick {$currentTick} (No Healing): {$entropy}");

        // 5. Run Faction Agent (Should drift towards Militarism)
        $this->info("Running Faction Agent Turn...");
        $factionAgent->executeTurn($faction, $world, $currentTick);
        
        $faction->refresh();
        $this->info("Faction Militarism after Drift: {$faction->getIdeology()->militarism} (Expected > 0.1)");

        // 6. Create Institution (The United Nations)
        $institution = Institution::create([
            'id' => Str::uuid(),
            'world_id' => $world->id,
            'name' => 'Global Peacekeepers',
            'type' => 'bureaucracy',
            'charter_values' => ['militarism' => 0.0, 'collectivism' => 0.8],
            'public_trust' => 0.9,
            'authority_level' => 0.8,
            'created_tick' => 100
        ]);
        $this->info("Institution Created: {$institution->name}");

        // 7. Perform Healing
        $this->info("Institution attempting to heal the Scar...");
        $healingEvent = $healingService->performHealing(
            $institution,
            $scar,
            $currentTick,
            ['diplomacy' => 1.0]
        );
        $this->info("Healing Event Created. Effectiveness: {$healingEvent->effectiveness_score}");

        // 8. Check Entropy (After Healing)
        $entropyAfter = $entropyCalc->calculateWorldEntropy($world, $currentTick);
        $this->info("Entropy at Tick {$currentTick} (After Healing): {$entropyAfter}");

        if ($entropyAfter < $entropy) {
            $this->info("SUCCESS: Entropy reduced by healing.");
        } else {
            $this->error("FAILURE: Entropy did not decrease.");
        }
    }
}
