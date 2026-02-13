<?php

namespace App\Domains\Vietnamese\Services;

use App\Models\World;
use App\Domains\Vietnamese\Models\VietnameseHero;
use App\Domains\World\Services\WorldForkService;
use Illuminate\Support\Facades\Log;

class HeroBifurcationService
{
    public function __construct(
        private VietnameseOriginService $originService,
        private CosmicIntegrationService $cosmicService,
        private WorldForkService $forkService
    ) {}

    /**
     * Check active heroes in the current era for bifurcation triggers
     */
    public function checkHeroTriggers(World $world, int $currentEra): ?array
    {
        // Only valid for Vietnamese origin worlds
        if ($world->origin_type !== 'vietnamese') {
            return null;
        }

        // Get active heroes in this era
        $activeHeroes = $this->originService->getActiveHeroesInEra($world, $currentEra);
        
        foreach ($activeHeroes as $heroData) {
            // Hero data array from service might be just array, need Model for CosmicService
            $hero = VietnameseHero::find($heroData['hero_id'] ?? $heroData['id']);
            
            if (!$hero) continue;

            // Use CosmicIntegrationService to check bifurcation potential
            $bifurcation = $this->cosmicService->checkBifurcationTrigger($hero);
            
            // Threshold for auto-triggering (e.g., probability > 80%)
            if ($bifurcation && ($bifurcation['probability'] ?? 0) > 0.8) {
                return $this->triggerHeroBifurcation($world, $hero, $bifurcation);
            }
        }
        
        return null;
    }
    
    /**
     * Trigger a world bifurcation based on a hero's impact
     * Creates 2 branches: Victory Path (A) and Struggle Path (B)
     */
    private function triggerHeroBifurcation(World $world, VietnameseHero $hero, array $bifurcation): array
    {
        Log::info("Hero {$hero->name} triggered bifurcation in World {$world->id}: {$bifurcation['type']}");
        
        $tick = $world->current_tick ?? 0;
        
        // 1. Branch A: The "Victory/Ascension" Path
        // Hero succeeds completely, ushering in a new era
        $branchA = $this->forkService->fork(
            $world,
            $tick,
            "{$world->name} - {$hero->name} (Victory Path)"
        );
        
        // Apply Branch A Modifiers (High Energy, New Order)
        $branchA->update([
            'cosmic_energy' => $world->cosmic_energy * 1.15, // Boost energy
            'cosmic_entropy' => $world->cosmic_entropy * 0.9, // Stabilize entropy
            'bifurcation_era' => $world->current_era,
            'bifurcation_trigger' => "hero:{$hero->id}:victory",
            'bifurcation_type' => $bifurcation['type'],
            'origin_metadata' => array_merge($world->origin_metadata ?? [], [
                'dominant_hero' => $hero->name,
                'path_type' => 'victory'
            ])
        ]);

        // 2. Branch B: The "Struggle/Resistance" Path
        // Hero becomes a martyr or struggles, chaos increases
        $branchB = $this->forkService->fork(
            $world,
            $tick,
            "{$world->name} - {$hero->name} (Struggle Path)"
        );
        
        // Apply Branch B Modifiers (High Entropy, Conflict)
        $branchB->update([
            'cosmic_energy' => $world->cosmic_energy * 0.9, // Drain energy
            'cosmic_entropy' => $world->cosmic_entropy * 1.15, // Increase chaos
            'bifurcation_era' => $world->current_era,
            'bifurcation_trigger' => "hero:{$hero->id}:struggle",
            'bifurcation_type' => $bifurcation['type'],
            'origin_metadata' => array_merge($world->origin_metadata ?? [], [
                'dominant_hero' => $hero->name, 
                'path_type' => 'struggle'
            ])
        ]);
        
        return [
            'trigger_hero' => $hero->name,
            'branch_a' => $branchA,
            'branch_b' => $branchB,
        ];
    }
}
