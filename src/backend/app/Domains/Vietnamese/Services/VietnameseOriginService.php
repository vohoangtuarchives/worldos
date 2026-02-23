<?php

namespace App\Domains\Vietnamese\Services;

use App\Models\World;
use App\Domains\Vietnamese\Models\VietnameseHero;
use Illuminate\Support\Facades\DB;

class VietnameseOriginService
{
    /**
     * Create a world with Vietnamese mythology origin (Trăm Trứng)
     */
    public function createVietnameseWorld(array $params): World
    {
        return DB::transaction(function () use ($params) {
            // Create world with Vietnamese origin metadata
            $world = World::create([
                'name' => $this->ensureUniqueName($params['name'] ?? 'Việt Nam - Trăm Trứng'),
                'origin_type' => 'vietnamese',
                'chaos_seed' => $params['chaos_seed'] ?? random_int(1, 999999),
                
                // Chaos parameters
                'initial_entropy' => $params['initial_entropy'] ?? 0.95,
                'initial_energy' => $params['initial_energy'] ?? 0.80,
                'initial_stability' => $params['initial_stability'] ?? 0.10,
                
                // Current state (starts from primordial chaos)
                'cosmic_energy' => $params['initial_energy'] ?? 0.80,
                'cosmic_entropy' => $params['initial_entropy'] ?? 0.95,
                'cosmic_stability' => $params['initial_stability'] ?? 0.10,
                
                'current_era' => 0,
                'yggdrasil_realm' => 'TRUNK',
                
                // Required fields (origin-based worlds don't use preset system)
                'preset' => 'vietnamese_mythology',
                'gene_vector' => [
                    'dominance' => 0.5,
                    'mutation_rate' => 0.1,
                    'is_dominant' => true,
                    'traits' => ['dragon_bloodline', 'fairy_aura', 'adaptation'],
                ],
                // 'type' removed as column does not exist
                'status' => 'active',
                'autonomous' => true,
                'health_status' => \WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus::STABLE->value,
                
                // Vietnamese-specific metadata
                'origin_metadata' => [
                    'mythology' => 'vietnamese_tram_trung',
                    'progenitors' => [
                        'father' => 'Lạc Long Quân (龍君)',
                        'mother' => 'Âu Cơ (嫗姬)',
                    ],
                    'origin_story' => '100 eggs → 50 children to mountains, 50 to sea',
                    'egg_count' => 100,
                    'mountain_sea_split_era' => null, // Determined by entropy
                    'activated_heroes' => [],
                ],
            ]);
            
            // Seed mythological heroes (Era 0-10)
            $this->seedMythologicalHeroes($world);
            
            // Schedule primordial bifurcation (Mountain vs Sea split)
            $this->scheduleMountainSeaBifurcation($world);
            
            return $world;
        });
    }
    
    /**
     * Seed initial mythological heroes active in world
     */
    private function seedMythologicalHeroes(World $world): void
    {
        $mythHeroes = VietnameseHero::where('period', 'MYTHICAL')
            ->where('era', '<=', 10)
            ->get();
        
        $activatedHeroes = [];
        
        foreach ($mythHeroes as $hero) {
            // Activate hero in this world
            $activatedHeroes[] = [
                'hero_id' => $hero->id,
                'hero_name' => $hero->name,
                'activation_era' => $hero->era ?? 0,
                'archetype' => $hero->archetype,
                'impact_score' => $hero->impact_score,
            ];
        }
        
        // Store in world metadata
        $metadata = $world->origin_metadata;
        $metadata['activated_heroes'] = $activatedHeroes;
        $world->update(['origin_metadata' => $metadata]);
    }
    
    /**
     * Schedule the primordial Mountain/Sea split bifurcation
     * 
     * Entropy determines timing:
     * - High entropy (0.9+) → split delayed to Era 15-20
     * - Medium (0.5-0.9) → split at Era 10-15
     * - Low (<0.5) → split early at Era 5-10
     */
    private function scheduleMountainSeaBifurcation(World $world): void
    {
        $entropy = $world->initial_entropy;
        
        // Calculate split era based on entropy
        $splitEra = (int) ceil($entropy * 15) + 5; // 5-20 range
        
        // Store in metadata
        $metadata = $world->origin_metadata;
        $metadata['mountain_sea_split_era'] = $splitEra;
        $world->update(['origin_metadata' => $metadata]);
        
        // TODO: Create WorldBifurcationSchedule entry
        // This will be triggered by simulation loop when reaching split era
    }
    
    /**
     * Get active heroes for a specific era in this Vietnamese world
     */
    public function getActiveHeroesInEra(World $world, int $era): array
    {
        if ($world->origin_type !== 'vietnamese') {
            return [];
        }
        
        // Get all Vietnamese heroes whose birth-death spans this era
        return VietnameseHero::where(function ($query) use ($era) {
            $eraYearStart = $era * 50;
            $eraYearEnd = ($era + 1) * 50;
            
            $query->where(function ($q) use ($eraYearStart, $eraYearEnd) {
                // Hero born before era ends AND (no death year OR died after era starts)
                $q->where('birth_year', '<=', $eraYearEnd)
                  ->where(function ($q2) use ($eraYearStart) {
                      $q2->whereNull('death_year')
                         ->orWhere('death_year', '>=', $eraYearStart);
                  });
            });
        })
        ->orWhere('period', 'MYTHICAL') // Mythical heroes always active
        ->get()
        ->toArray();
    }
    
    /**
     * Check if Mountain/Sea split should trigger
     */
    public function shouldTriggerMountainSeaSplit(World $world): bool
    {
        if ($world->origin_type !== 'vietnamese') {
            return false;
        }
        
        $metadata = $world->origin_metadata;
        $splitEra = $metadata['mountain_sea_split_era'] ?? null;
        
        if (!$splitEra) {
            return false;
        }
        
        return $world->current_era >= $splitEra;
    }
    
    /**
     * Execute Mountain/Sea bifurcation
     */
    public function executeMountainSeaBifurcation(World $parent): array
    {
        // Branch A: Mountain (Lạc people - wet rice, Red River Delta)
        $mountainWorld = World::create([
            'parent_id' => $parent->id,
            'name' => "{$parent->name} - Mountain Branch (Lạc Việt)",
            'origin_type' => 'vietnamese',
            'bifurcation_era' => $parent->current_era,
            'bifurcation_type' => 'VIETNAMESE_MOUNTAIN_SEA_SPLIT',
            'bifurcation_trigger' => 'primordial_split:mountain',
            
            // Mountain branch: higher governance (agricultural society)
            'cosmic_energy' => $parent->cosmic_energy * 0.95,
            'cosmic_entropy' => $parent->cosmic_entropy * 0.85, // More order
            'cosmic_stability' => $parent->cosmic_stability * 1.2,
            
            'current_era' => $parent->current_era,
            'yggdrasil_realm' => 'TRUNK',
            
            'origin_metadata' => array_merge($parent->origin_metadata, [
                'branch_type' => 'mountain',
                'progenitor_children' => 50,
                'geographic_focus' => 'Red River Delta, Highlands',
                'cultural_traits' => ['agriculture', 'settled', 'governance'],
            ]),
        ]);
        
        // Branch B: Sea (Âu people - maritime, Southern expansion)
        $seaWorld = World::create([
            'parent_id' => $parent->id,
            'name' => "{$parent->name} - Sea Branch (Âu Việt)",
            'origin_type' => 'vietnamese',
            'bifurcation_era' => $parent->current_era,
            'bifurcation_type' => 'VIETNAMESE_MOUNTAIN_SEA_SPLIT',
            'bifurcation_trigger' => 'primordial_split:sea',
            
            // Sea branch: higher exploration/territory
            'cosmic_energy' => $parent->cosmic_energy * 1.05,
            'cosmic_entropy' => $parent->cosmic_entropy * 1.1, // More chaos (exploration)
            'cosmic_stability' => $parent->cosmic_stability * 0.9,
            
            'current_era' => $parent->current_era,
            'yggdrasil_realm' => 'TRUNK',
            
            'origin_metadata' => array_merge($parent->origin_metadata, [
                'branch_type' => 'sea',
                'progenitor_children' => 50,
                'geographic_focus' => 'Coastal, Southeast expansion',
                'cultural_traits' => ['maritime', 'nomadic', 'territory_expansion'],
            ]),
        ]);
        
        return [
            'mountain' => $mountainWorld,
            'sea' => $seaWorld,
        ];
    }
    /**
     * Ensure the world name is unique by appending a counter if necessary
     */
    private function ensureUniqueName(string $name): string
    {
        $originalName = $name;
        $counter = 1;
        
        while (World::where('name', $name)->exists()) {
            $name = "{$originalName} ({$counter})";
            $counter++;
        }
        
        return $name;
    }
}
