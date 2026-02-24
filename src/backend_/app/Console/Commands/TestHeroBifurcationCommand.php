<?php

namespace App\Console\Commands;

use App\Models\World;
use WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero;
use WorldOS\Legacy\Application\Vietnamese\Services\HeroBifurcationService;
use WorldOS\Legacy\Application\Vietnamese\Services\CosmicIntegrationService;
use Illuminate\Console\Command;

class TestHeroBifurcationCommand extends Command
{
    protected $signature = 'world:test-bifurcation {world_id} {hero_name}';
    protected $description = 'Test hero-driven bifurcation manually';
    
    public function handle(HeroBifurcationService $bifService, CosmicIntegrationService $cosmicService): int
    {
        $worldId = $this->argument('world_id');
        $heroName = $this->argument('hero_name');
        
        $world = World::find($worldId);
        if (!$world) {
            $this->error("World not found!");
            return Command::FAILURE;
        }
        
        // Find hero
        $hero = VietnameseHero::where('name', 'LIKE', "%{$heroName}%")->first();
        if (!$hero) {
            $this->error("Hero not found!");
            return Command::FAILURE;
        }
        
        $this->info("Checking bifurcation for hero: {$hero->name} in world {$world->name}...");
        
        // Check probability
        $bifurcation = $cosmicService->checkBifurcationTrigger($hero);
        if (!$bifurcation) {
            $this->warn("Hero does not meet bifurcation criteria.");
            return Command::FAILURE;
        }
        
        $this->info("Bifurcation detected: " . json_encode($bifurcation));
        $this->info("Triggering split...");
        
        // Manual reflection access to private method or use public wrapper
        // For testing, I'll temporarily use reflection or expose a public method?
        // Actually, checkHeroTriggers calls triggerHeroBifurcation.
        // But checkHeroTriggers requires the hero to be active in the era.
        // I'll simulate by setting world era to hero era.
        
        $world->current_era = $hero->era ?? 50;
        $world->save();
        
        $result = $bifService->checkHeroTriggers($world, $world->current_era);
        
        if ($result) {
            $this->info("✅ Bifurcation Successful!");
            $this->info("Branch A (Victory): {$result['branch_a']->name} (ID: {$result['branch_a']->id})");
            $this->info("Branch B (Struggle): {$result['branch_b']->name} (ID: {$result['branch_b']->id})");
        } else {
            $this->error("Failed to trigger bifurcation via service.");
        }
        
        return Command::SUCCESS;
    }
}
