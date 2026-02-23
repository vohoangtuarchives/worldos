<?php

namespace App\Domains\Vietnamese\Listeners;

use App\Domains\Evolution\Events\WorldTicked;
use App\Domains\Vietnamese\Services\HeroMaterialBridge;
use App\Domains\Vietnamese\Models\VietnameseHero; 
use App\Domains\Vietnamese\Models\Hero;
use App\Domains\Cosmology\Entities\WorldStateVector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckHeroSpawningListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly HeroMaterialBridge $bridge,
        private readonly \App\Domains\Vietnamese\Factories\HeroFactory $heroFactory
    ) {}

    public function handle(WorldTicked $event): void
    {
        $state = $event->state;
        $world = $event->world;
        $dimensions = $state->getAll();

        // Guard: Only apply Vietnamese mechanics if relevant context exists
        $isVietnamese = ($world->origin_type === 'vietnamese') || 
                        in_array('vietnamese', $world->tags ?? []) ||
                        in_array('dong_son', $world->tags ?? []);

        // If not explicitly tagged, check if any Vietnamese-named faction exists
        if (!$isVietnamese) {
            $vietnameseNames = [
                'Bách Việt', 'Bach Viet',
                'Văn Lang', 'Van Lang',
                'Âu Lạc', 'Au Lac', 
                'Vạn Xuân', 'Van Xuan',
                'Đại Cồ Việt', 'Dai Co Viet',
                'Đại Việt', 'Dai Viet',
                'Việt Nam', 'Viet Nam',
                'An Nam', 'Annam'
            ];

            $hasVietnameseFaction = \App\Models\Faction::where('world_id', $world->id)
                ->where(function ($query) use ($vietnameseNames) {
                    foreach ($vietnameseNames as $name) {
                        $query->orWhere('name', 'LIKE', "%{$name}%");
                    }
                })
                ->exists();
            
            if ($hasVietnameseFaction) {
                $isVietnamese = true;
            }
        }

        if (!$isVietnamese) {
            return;
        }

        // 1. Check for Chaos/Entropy Threshold -> Spawn REBEL/SAVIOR
        if (($dimensions[WorldStateVector::DIMENSION_ENTROPY] ?? 0) > 0.8) {
            $this->attemptSpawn($world, ['REBEL_LEADER', 'EMERGENCY_SAVIOR'], 'High Entropy Trigger', $dimensions);
        }

        // 2. Check for Order/Tyranny Threshold -> Spawn REFORMER
        if (($dimensions[WorldStateVector::DIMENSION_ORDER] ?? 0) > 0.9) {
            $this->attemptSpawn($world, ['REFORMER', 'PHILOSOPHER_KING'], 'High Order Trigger', $dimensions);
        }
        
        // 3. Check for Cultural Decay -> Spawn CULTURAL_HERO
        if (($dimensions[WorldStateVector::DIMENSION_COHESION] ?? 0) < 0.3) {
            $this->attemptSpawn($world, ['CULTURAL_HERO', 'CULTURAL_SOUL_ARCHITECT'], 'Low Cohesion Trigger', $dimensions);
        }
    }

    private function attemptSpawn($world, array $allowedArchetypes, string $reason, array $dimensions): void
    {
        // Simple cooldown check using Cache or World meta would be better, but for now random chance
        // to avoid spamming heroes every tick.
        if (rand(1, 100) > 5) { // 5% chance per tick if condition met
            return;
        }

        // Try to find seeded hero first (70% chance)
        $seededHero = null;
        if (rand(1, 100) <= 70) {
            $seededHero = VietnameseHero::whereIn('archetype', $allowedArchetypes)
                ->inRandomOrder()
                ->first();
        }

        $hero = null;

        // If Seeded Hero found, convert to World Hero (Runtime)
        if ($seededHero) {
            // Check if already exists in world to avoid duplicates if unique? 
            // For now allow duplicates (reincarnation/legacy) or check name.
            // Let's create a new runtime instance.
            $hero = \App\Domains\Vietnamese\Models\Hero::create([
                'world_id' => $world->id,
                'name' => $seededHero->name,
                'other_names' => $seededHero->other_names,
                'archetype' => $seededHero->archetype,
                'dimensions' => $seededHero->dimensions,
                'impact_score' => $seededHero->impact_score,
                'biography' => $seededHero->biography,
                'era' => $seededHero->era,
                'origin_hero_id' => $seededHero->id,
                'is_generated' => false,
                'status' => 'active',
                'spawned_at_tick' => $world->tick ?? 0
            ]);
            $reason .= " (Historical)";
        }

        // Use Procedural Generation if no seeded hero found, or 30% chance
        if (!$hero) {
            // Pick random archetype from allowed
            $archetype = $allowedArchetypes[array_rand($allowedArchetypes)];
            $hero = $this->heroFactory->createProceduralHero((string)$world->id, $dimensions, $archetype);
            $reason .= " (Procedurally Generated)";
        }

        if (!$hero) {
            return;
        }

        // Apply Resonance
        Log::info("Spawning Hero {$hero->name} due to {$reason} in World {$world->id}");
        $effects = $this->bridge->processHeroEmergence($hero, $world); // Bridge now accepts Hero
        
        // Log effect
        Log::info("Hero Effects Applied:", $effects);
    }
}
