<?php

namespace App\Services\Simulation;

use App\Models\Universe;
use App\Models\InstitutionalEntity;
use App\Models\MythScar;

/**
 * Institutional Engine: Manages lifecycle of Social/Political Entities 
 * (Factions, Guilds, Orders) as per WORLDOS_V6 §4.5.
 */
class InstitutionalEngine
{
    /*
     * Process tick for all entities in the universe.
     */
    public function process(Universe $universe, int $tick, array $metrics = []): void
    {
        $entities = InstitutionalEntity::where('universe_id', $universe->id)
            ->whereNull('collapsed_at_tick')
            ->get();

        $activeEdicts = $metrics['active_edicts'] ?? [];

        foreach ($entities as $entity) {
            $this->updateEntity($entity, $universe, $tick, $activeEdicts);
        }

        // Potential spawning of new entities based on cultural threshold
        $this->potentialSpawn($universe, $tick, $activeEdicts);

        // Evolve social contracts
        if (isset($this->evolutionAction)) {
            $this->evolutionAction->execute($universe, $tick);
        }
    }

    protected ?\App\Actions\Simulation\SocialContractEvolutionAction $evolutionAction = null;

    public function setEvolutionAction(\App\Actions\Simulation\SocialContractEvolutionAction $action)
    {
        $this->evolutionAction = $action;
    }

    protected function updateEntity(InstitutionalEntity $entity, Universe $universe, int $tick, array $activeEdicts): void
    {
        $vec = $universe->state_vector;
        $zones = $vec['zones'] ?? [];
        $influenceMap = $entity->influence_map ?? [];
        
        $totalSupport = 0.0;
        $newInfluenceMap = [];

        // Edict Multipliers
        $capacityMultiplier = 1.0;
        $growthMultiplier = 1.0;

        if (isset($activeEdicts['heavenly_tribulation'])) {
            $capacityMultiplier *= 0.8; // Institutions suffer from chaos
        }
        if (isset($activeEdicts['reiki_revival'])) {
            $capacityMultiplier *= 1.2; // Higher energy allows more complex structures
            $growthMultiplier *= 1.5;
        }
        if (isset($activeEdicts['age_of_chaos'])) {
            $growthMultiplier *= 0.5; // Hard to expand in chaos
        }

        foreach ($zones as $zone) {
            $zoneId = $zone['id'];
            $currentInfluence = $influenceMap[$zoneId] ?? 0.0;
            
            // Legitimacy check: alignment between entity ideology and zone culture
            $alignment = $this->calculateAlignment($entity->ideology_vector, $zone['culture'] ?? []);
            
            // Influence grows/shrinks based on alignment and existing presence
            if ($alignment > 0.5) {
                $growth = 0.02 * $alignment * $growthMultiplier;
            } else {
                $growth = -0.01 * (1.0 - $alignment);
            }

            $newInfluenceMap[$zoneId] = max(0.0, min(1.0, $currentInfluence + $growth));
            $totalSupport += $newInfluenceMap[$zoneId];
        }

        // Maintenance cost based on institutional memory and scale
        $maintenance = (count($zones) * 0.05) * (1.0 - $entity->institutional_memory * 0.5);
        
        $change = ($totalSupport * 0.1 * $capacityMultiplier) - $maintenance;
        $entity->org_capacity = max(0.0, $entity->org_capacity + $change);
        $entity->influence_map = $newInfluenceMap;
        
        // Memory decay - boosted by divine inspiration
        $memoryDecay = isset($activeEdicts['divine_inspiration']) ? 0.9999 : 0.999;
        $entity->institutional_memory = max(0.1, $entity->institutional_memory * $memoryDecay);

        if ($entity->org_capacity <= 1.0) {
            // Very weak, risk of collapse
            $collapseChance = 5;
            if (isset($activeEdicts['age_of_chaos'])) $collapseChance = 20;

            if (mt_rand(0, 100) < $collapseChance) {
                $this->collapse($entity, $tick);
                return;
            }
        }

        $entity->save();
    }

    protected function potentialSpawn(Universe $universe, int $tick, array $activeEdicts): void
    {
        $vec = $universe->state_vector;
        $zones = $vec['zones'] ?? [];
        
        // Reiki Revival boosts spawn chance
        $spawnCheckRate = isset($activeEdicts['reiki_revival']) ? 4 : 9; // 0..4 or 0..9

        if (mt_rand(0, $spawnCheckRate) > 0) return;

        foreach ($zones as $zone) {
            $myth = $zone['culture']['myth'] ?? 0;
            $respect = $zone['culture']['respect'] ?? 0;
            $tradition = $zone['culture']['tradition'] ?? 0;

            // Cults emerge from myth/respect
            if ($myth > 0.8 && $respect > 0.7) {
                $this->spawn($universe, $zone['id'], $tick, 'cult');
                return; 
            }

            // Orders emerge from tradition/respect
            if ($tradition > 0.8 && $respect > 0.8) {
                $this->spawn($universe, $zone['id'], $tick, 'order');
                return;
            }
        }
    }

    protected function spawn(Universe $universe, int $zoneId, int $tick, string $type): void
    {
        $namePrefixes = ['Holy', 'Imperial', 'Eternal', 'Shadow', 'Azure'];
        $nameSuffixes = ['Order', 'Guild', 'Federation', 'Cult', 'Monastery'];
        $name = $namePrefixes[array_rand($namePrefixes)] . ' ' . $nameSuffixes[array_rand($nameSuffixes)];

        InstitutionalEntity::create([
            'universe_id' => $universe->id,
            'name' => $name . ' ' . mt_rand(10, 99),
            'entity_type' => $type,
            'ideology_vector' => $this->randomIdeology(),
            'org_capacity' => 10.0,
            'influence_map' => ["$zoneId" => 0.2],
            'spawned_at_tick' => $tick,
        ]);
    }

    protected function collapse(InstitutionalEntity $entity, int $tick): void
    {
        $entity->update(['collapsed_at_tick' => $tick]);
        
        // Create Myth Scar if it was somewhat significant
        if ($entity->org_capacity > 2.0 || mt_rand(0, 1) === 1) {
            MythScar::create([
                'universe_id' => $entity->universe_id,
                'name' => "Fall of " . $entity->name,
                'slug' => 'collapse_' . $entity->id,
                'origin_tick' => $tick,
                'intensity' => 0.5,
                'scar_type' => 'institutional_collapse',
            ]);
        }
    }

    protected function calculateAlignment(array $ideology, array $culture): float
    {
        if (empty($ideology) || empty($culture)) return 0.5;
        
        $diff = 0.0;
        $count = 0;
        foreach ($ideology as $key => $val) {
            if (isset($culture[$key])) {
                $diff += abs($val - $culture[$key]);
                $count++;
            }
        }
        
        if ($count === 0) return 0.5;
        return 1.0 - ($diff / $count);
    }

    protected function randomIdeology(): array
    {
        return [
            'tradition' => (mt_rand(0, 100) / 100.0),
            'innovation' => (mt_rand(0, 100) / 100.0),
            'trust' => (mt_rand(0, 100) / 100.0),
            'violence' => (mt_rand(0, 100) / 100.0),
            'respect' => (mt_rand(0, 100) / 100.0),
            'myth' => (mt_rand(0, 100) / 100.0),
        ];
    }
}
