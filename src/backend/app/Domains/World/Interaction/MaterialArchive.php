<?php

namespace App\Domains\World\Interaction;

use App\Models\MaterialSeed;

class MaterialArchive
{
    private array $materials = [];
    private array $catalog = [];
    private array $rarityDistribution = [];

    public function __construct()
    {
        $this->initializeCatalog();
    }

    public function addMaterial(array $material): void
    {
        $materialId = $material['id'];
        $this->materials[$materialId] = $material;
        
        // Update catalog
        $type = $material['type'];
        $archetype = $material['archetype'];
        
        if (!isset($this->catalog[$type])) {
            $this->catalog[$type] = [];
        }
        
        $this->catalog[$type][$materialId] = $material;
        
        // Update rarity distribution
        $rarity = $material['rarity'];
        $this->rarityDistribution[$rarity] = ($this->rarityDistribution[$rarity] ?? 0) + 1;
    }

    public function getMaterial(string $materialId): ?array
    {
        return $this->materials[$materialId] ?? null;
    }

    public function getMaterialsByType(string $type): array
    {
        return $this->catalog[$type] ?? [];
    }

    public function getMaterialsByArchetype(string $archetype): array
    {
        $results = [];
        foreach ($this->catalog as $type => $materials) {
            foreach ($materials as $material) {
                if ($material['archetype'] === $archetype) {
                    $results[] = $material;
                }
            }
        }
        return $results;
    }

    public function getMaterialsByRarity(string $rarity): array
    {
        $results = [];
        foreach ($this->materials as $material) {
            if ($material['rarity'] === $rarity) {
                $results[] = $material;
            }
        }
        return $results;
    }

    public function getHighValueMaterials(int $limit = 10): array
    {
        $allMaterials = array_values($this->materials);
        
        // Sort by story potential and relevance
        usort($allMaterials, function ($a, $b) {
            $scoreA = ($a['story_potential'] * 0.6) + ($a['relevance_score'] * 0.4);
            $scoreB = ($b['story_potential'] * 0.6) + ($b['relevance_score'] * 0.4);
            return $scoreB <=> $scoreA;
        });
        
        return array_slice($allMaterials, 0, $limit);
    }

    public function getMaterialStatistics(): array
    {
        $stats = [
            'total_materials' => count($this->materials),
            'type_distribution' => [],
            'archetype_distribution' => [],
            'rarity_distribution' => $this->rarityDistribution,
            'average_tension' => 0,
            'high_value_count' => 0
        ];

        foreach ($this->catalog as $type => $materials) {
            $stats['type_distribution'][$type] = count($materials);
        }

        foreach ($this->materials as $material) {
            $archetype = $material['archetype'];
            $stats['archetype_distribution'][$archetype] = ($stats['archetype_distribution'][$archetype] ?? 0) + 1;
            $stats['average_tension'] += $material['tension_level'];
            
            if ($material['story_potential'] > 0.8) {
                $stats['high_value_count']++;
            }
        }

        if (count($this->materials) > 0) {
            $stats['average_tension'] /= count($this->materials);
        }

        return $stats;
    }

    public function generateMaterialReport(): array
    {
        $report = [
            'summary' => $this->getMaterialStatistics(),
            'legendary_materials' => $this->getMaterialsByRarity('legendary'),
            'epic_discoveries' => $this->getMaterialsByRarity('epic'),
            'rare_finds' => $this->getMaterialsByRarity('rare'),
            'story_seeds' => $this->getHighValueMaterials(5),
            'catalog_overview' => $this->generateCatalogOverview()
        ];

        return $report;
    }

    public function findStoryCombinations(array $materials): array
    {
        $combinations = [];
        $count = count($materials);
        
        if ($count < 2) return $combinations;

        // Find materials that work well together
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $materialA = $materials[$i];
                $materialB = $materials[$j];
                
                $compatibility = $this->calculateMaterialCompatibility($materialA, $materialB);
                
                if ($compatibility > 0.6) {
                    $combinations[] = [
                        'materials' => [$materialA['id'], $materialB['id']],
                        'compatibility' => $compatibility,
                        'story_type' => $this->determineStoryType($materialA, $materialB),
                        'potential' => ($materialA['story_potential'] + $materialB['story_potential']) * $compatibility,
                        'themes' => $this->extractCombinedThemes($materialA, $materialB)
                    ];
                }
            }
        }

        // Sort by potential
        usort($combinations, fn($a, $b) => $b['potential'] <=> $a['potential']);
        
        return array_slice($combinations, 0, 10);
    }

    public function exportToDatabase(): void
    {
        foreach ($this->materials as $material) {
            MaterialSeed::create([
                'world_state_id' => null, // Archive materials aren't tied to specific worlds
                'seed_type' => $material['type'],
                'source_axes' => json_encode([
                    'interaction_type' => 'multi_world',
                    'extraction_method' => 'material_archive'
                ]),
                'content' => json_encode($material['content']),
                'relevance_score' => $material['relevance_score'],
                'tension_level' => $material['tension_level'],
                'archetype' => $material['archetype']
            ]);
        }
    }

    private function calculateMaterialCompatibility(array $materialA, array $materialB): float
    {
        $typeCompatibility = $this->getTypeCompatibility($materialA['type'], $materialB['type']);
        $archetypeCompatibility = $this->getArchetypeCompatibility($materialA['archetype'], $materialB['archetype']);
        $tensionBalance = 1 - abs($materialA['tension_level'] - $materialB['tension_level']);
        
        return ($typeCompatibility * 0.4) + ($archetypeCompatibility * 0.4) + ($tensionBalance * 0.2);
    }

    private function getTypeCompatibility(string $typeA, string $typeB): float
    {
        $compatibilityMatrix = [
            'doubt_crystal' => [
                'paradox_orb' => 0.9,
                'faith_fragment' => 0.8,
                'logic_fragment' => 0.7,
                'scarcity_essence' => 0.3
            ],
            'paradox_orb' => [
                'doubt_crystal' => 0.9,
                'logic_fragment' => 0.8,
                'reality_fragment' => 0.7,
                'creation_seed' => 0.6
            ],
            'scarcity_essence' => [
                'influence_dust' => 0.8,
                'conflict_catalyst' => 0.9,
                'power_fragment' => 0.7,
                'harmony_crystal' => 0.2
            ],
            'distortion_mirror' => [
                'reality_fragment' => 0.9,
                'creation_seed' => 0.8,
                'possibility_spark' => 0.7,
                'harmony_crystal' => 0.3
            ],
            'harmony_crystal' => [
                'fragment_shard' => 0.2,
                'convergence_matrix' => 0.8,
                'doubt_crystal' => 0.4,
                'paradox_orb' => 0.3
            ]
        ];
        
        return $compatibilityMatrix[$typeA][$typeB] ?? 0.1;
    }

    private function getArchetypeCompatibility(string $archetypeA, string $archetypeB): float
    {
        $compatibleArchetypes = [
            'faith_fragment' => ['logic_fragment', 'story_fragment', 'memory_fragment'],
            'logic_fragment' => ['faith_fragment', 'reality_fragment', 'creation_seed'],
            'conflict_catalyst' => ['power_fragment', 'influence_dust', 'stability_echo'],
            'reality_fragment' => ['creation_seed', 'chaos_manifestation', 'distortion_mirror'],
            'creation_seed' => ['reality_fragment', 'possibility_spark', 'evolution_catalyst'],
            'stability_echo' => ['harmony_crystal', 'faith_fragment', 'order_fragment'],
            'chaos_manifestation' => ['reality_fragment', 'entropy_vapor', 'disruption_seed']
        ];
        
        return in_array($archetypeB, $compatibleArchetypes[$archetypeA] ?? []) ? 0.8 : 0.2;
    }

    private function determineStoryType(array $materialA, array $materialB): string
    {
        $archetypes = [$materialA['archetype'], $materialB['archetype']];
        sort($archetypes);
        
        $storyTypeMap = [
            'faith_fragment,logic_fragment' => 'philosophical_debate',
            'conflict_catalyst,power_fragment' => 'power_struggle',
            'reality_fragment,creation_seed' => 'reality_transformation',
            'stability_echo,chaos_manifestation' => 'order_vs_chaos',
            'faith_fragment,reality_fragment' => 'crisis_of_faith',
            'logic_fragment,creation_seed' => 'technological_evolution'
        ];
        
        $key = implode(',', $archetypes);
        return $storyTypeMap[$key] ?? 'generic_story';
    }

    private function extractCombinedThemes(array $materialA, array $materialB): array
    {
        $themes = [];
        
        // Extract themes from content
        $contentA = $materialA['content'] ?? [];
        $contentB = $materialB['content'] ?? [];
        
        if (isset($contentA['narrative_potential']['themes'])) {
            $themes = array_merge($themes, $contentA['narrative_potential']['themes']);
        }
        
        if (isset($contentB['narrative_potential']['themes'])) {
            $themes = array_merge($themes, $contentB['narrative_potential']['themes']);
        }
        
        return array_unique($themes);
    }

    private function generateCatalogOverview(): array
    {
        $overview = [];
        
        foreach ($this->catalog as $type => $materials) {
            $overview[$type] = [
                'count' => count($materials),
                'average_tension' => array_sum(array_column($materials, 'tension_level')) / count($materials),
                'highest_potential' => max(array_column($materials, 'story_potential')),
                'rarest_material' => $this->findRarestInType($materials)
            ];
        }
        
        return $overview;
    }

    private function findRarestInType(array $materials): array
    {
        $rarest = null;
        $lowestRarityScore = PHP_FLOAT_MAX;
        
        $rarityScores = [
            'common' => 1,
            'uncommon' => 2,
            'rare' => 3,
            'epic' => 4,
            'legendary' => 5
        ];
        
        foreach ($materials as $material) {
            $score = $rarityScores[$material['rarity']] ?? 1;
            if ($score < $lowestRarityScore) {
                $lowestRarityScore = $score;
                $rarest = $material;
            }
        }
        
        return $rarest ?? $materials[0] ?? [];
    }

    private function initializeCatalog(): void
    {
        $this->catalog = [];
        $this->rarityDistribution = [
            'common' => 0,
            'uncommon' => 0,
            'rare' => 0,
            'epic' => 0,
            'legendary' => 0
        ];
    }
}
