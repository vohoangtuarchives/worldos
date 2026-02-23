<?php

namespace WorldOS\Legacy\Application\Vietnamese\Listeners;

use App\Domains\Vietnamese\Events\HeroCreated;
use WorldOS\Legacy\Application\Vietnamese\Services\HeroMaterialBridge;
use App\Models\World;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ApplyHeroImpactListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly HeroMaterialBridge $bridge
    ) {}

    public function handle(HeroCreated $event): void
    {
        $hero = $event->hero;
        // Assuming World ID is accessible via Hero or Context. 
        // Heroes in this system might trigger global effects or specific world effects.
        // For now, let's assume we find the world context from the hero's creation event 
        // or we need to pass World ID in the event.
        
        // If Hero doesn't have world_id, we might skip. 
        // But VietnameseHero model doesn't seem to have world_id column in the view I saw earlier? 
        // It has 'era', 'period'. 
        // WorldOS v3 implies Multi-World. 
        // If Hero is Global (Seeder), this listener might apply to the "Active World".
        // Let's assume the Event carries the World ID.

        if (!isset($event->worldId)) {
            return;
        }

        $world = World::find($event->worldId);
        if (!$world) {
            return;
        }

        $effects = $this->bridge->processHeroEmergence($hero, $world);

        if (!empty($effects)) {
            Log::info("Hero {$hero->name} impacted World {$world->id} materials:", $effects);
        }
    }
}
