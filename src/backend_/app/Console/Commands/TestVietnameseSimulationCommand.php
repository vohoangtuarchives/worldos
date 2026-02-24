<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Saga\Domain\Legacy\SagaRunner;
use WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero;

class TestVietnameseSimulationCommand extends Command
{
    protected $signature = 'world:test-vietnamese-simulation {--epochs=10 : Number of epochs to simulate}';
    protected $description = 'Test the full simulation loop with Vietnamese Origin';

    public function handle()
    {
        $this->info("🚀 Starting Vietnamese Yggdrasil Simulation Test...");

        // 1. Create Test Saga
        $saga = Saga::create([
            'name' => 'Vietnamese Simulation Test ' . now()->timestamp,
            'status' => Saga::STATUS_PENDING,
            'world_count' => 1,
            'metadata' => [
                'origin_type' => 'vietnamese',
                'description' => 'Automated test for Vietnamese integration'
            ]
        ]);

        $this->info("✅ Created Test Saga: {$saga->name} (ID: {$saga->id})");

        // 2. Initialize Runner
        $runner = app(SagaRunner::class);
        $this->info("✅ Initialized SagaRunner");

        // 3. Create First World (using modified SagaRunner logic)
        // We trigger the runner manually step-by-step
        $this->info("🔄 Initializing World 1 (Vietnamese Origin)...");
        
        // This should trigger createNextWorld -> VietnameseOriginService
        // We simulate the loop manually to inspect state
        
        // Manually trigger first world creation logic since it's private in Runner
        // We can use runSync but we want to inspect between steps.
        // runSync loops until completion.
        
        // Alternative: Use reflection or just use runSync and rely on logs/database inspection after.
        // Let's use runSync for the first step to create the world, then inspect.
        
        // Actually, runSync runs the WHOLE saga. 
        // Let's create the world manually using the Service to ensure it works, 
        // then define it as the current world of the saga, then run simulation steps.
        
        $originService = app(\WorldOS\Legacy\Application\Vietnamese\Services\VietnameseOriginService::class);
        $world = $originService->createVietnameseWorld([
            'name' => "{$saga->name} - World 1",
            'chaos_seed' => 12345,
            'initial_entropy' => 0.8,
            'initial_energy' => 0.9,
        ]);
        
        // Link to Saga
        \WorldOS\Saga\Domain\Legacy\SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $world->id,
            'sequence' => 0,
            'status' => \WorldOS\Saga\Domain\Legacy\SagaWorld::STATUS_RUNNING,
        ]);
        
        $saga->update(['current_world_index' => 0, 'status' => Saga::STATUS_RUNNING]);
        
        $this->info("✅ Created World: {$world->name} (ID: {$world->id})");

        // FORCE RECALCULATION OF SCORES (to ensure test data is valid)
        $this->info("🔄 Recalculating Hero Scores...");
        $scoringService = app(\WorldOS\Legacy\Application\Vietnamese\Services\HeroScoringService::class);
        $version = \WorldOS\Legacy\Domain\Vietnamese\Models\ScoringVersion::active();
        
        if (!$version) {
            $this->warn("⚠️ No active scoring version found. Seeding v1.0...");
            \WorldOS\Legacy\Domain\Vietnamese\Models\ScoringVersion::create([
                'version' => '1.0',
                'weights' => [
                    'military' => 1.0,
                    'governance' => 1.0,
                    'culture' => 1.0,
                    'philosophy' => 0.8,
                ],
                'dimension_mapping' => [], // Default
                'is_active' => true,
            ]);
            $version = \WorldOS\Legacy\Domain\Vietnamese\Models\ScoringVersion::active();
        }

        $allHeroes = VietnameseHero::all();
        foreach ($allHeroes as $hero) {
            $dims = $scoringService->calculateAllDimensions($hero, $version);
            $score = $scoringService->calculateImpactScore($dims, $version);
            $hero->update([
                ...$dims,
                'impact_score' => $score,
                'scoring_version_id' => $version->id,
                'last_scored_at' => now(),
            ]);
        }
        $this->info("✅ Recalculated scores for {$allHeroes->count()} heroes.");
        
        // Verify Mythological Heroes
        $activeHeroes = $originService->getActiveHeroesInEra($world, 0);
        $this->info("📊 Era 0 Active Heroes: " . count($activeHeroes));
        foreach ($activeHeroes as $h) {
            $this->line("   - {$h['name']} (Score: {$h['impact_score']})");
        }

        // 4. Run Simulation Loop
        $epochs = (int) $this->option('epochs');
        $this->info("⏳ Simulating {$epochs} epochs (50 years each)...");

        $csmService = app(\WorldOS\Legacy\Application\Vietnamese\Services\CosmicIntegrationService::class);
        $bifService = app(\WorldOS\Legacy\Application\Vietnamese\Services\HeroBifurcationService::class);
        
        $headers = ['Era', 'Year', 'Active Heroes', 'Civ Boosts', 'Bifurcation Check'];
        $rows = [];

        for ($era = 0; $era < $epochs; $era++) {
            $year = $era * 50;
            $world->current_time = $year;
            $world->current_era = $era;
            $world->save();

            // Check Heroes
            $heroes = $originService->getActiveHeroesInEra($world, $era);
            $heroNames = implode(", ", array_map(fn($h) => $h['name'], array_slice($heroes, 0, 3)));
            if (count($heroes) > 3) $heroNames .= " + " . (count($heroes) - 3) . " more";

            // Check Boosts
            $boosts = $csmService->calculateEraCivilizationBoost($era);
            $boostSummary = [];
            foreach ($boosts as $k => $v) {
                if ($v > 0) $boostSummary[] = "$k:+" . number_format($v, 2);
            }
            $boostStr = implode(", ", $boostSummary);

            // Check Bifurcation
            $bifurcation = $bifService->checkHeroTriggers($world, $era);
            $bifStr = $bifurcation ? "⚠️ TRIGGER: " . $bifurcation['trigger_hero'] : "None";

            $rows[] = [$era, $year, $heroNames, $boostStr ?: 'None', $bifStr];
        }

        $this->table($headers, $rows);
        $this->info("✅ Simulation Test Complete.");
        
        // Cleanup if needed (optional)
        // $saga->delete();
    }
}
