<?php

namespace App\Domains\World\Interaction;

use App\Domains\World\WorldState;
use App\Domains\World\Interaction\InteractionZone;
use App\Models\World\WorldInteraction;
use App\Models\MaterialSeed;

class MaterialExtractor
{
    private array $extractionPatterns = [];
    private array $materialTemplates = [];

    public function __construct()
    {
        $this->initializeExtractionPatterns();
        $this->initializeMaterialTemplates();
    }

    public function extractFromInteraction(WorldInteraction $interaction): array
    {
        $materials = [];
        $type = $interaction->interaction_type;
        $strength = $interaction->strength;

        switch ($type) {
            case 'BELIEF_CONTAMINATION':
                $materials = $this->extractBeliefMaterials($interaction);
                break;
            case 'RESOURCE_CROSSFLOW':
                $materials = $this->extractResourceMaterials($interaction);
                break;
            case 'REALITY_DISTORTION':
                $materials = $this->extractRealityMaterials($interaction);
                break;
            case 'NARRATIVE_BLEED':
                $materials = $this->extractNarrativeMaterials($interaction);
                break;
        }

        return $materials;
    }

    public function extractFromZone(InteractionZone $zone): array
    {
        $materials = [];
        
        // Zone-level materials
        if ($zone->zone_coherence > 0.7) {
            $materials[] = $this->createMaterial(
                'harmony_crystal',
                'stability_artifact',
                $this->generateHarmonyContent($zone),
                0.3,
                $zone->zone_coherence
            );
        }

        if ($zone->zone_coherence < 0.4) {
            $materials[] = $this->createMaterial(
                'fragment_shard',
                'chaos_fragment',
                $this->generateFragmentContent($zone),
                0.8,
                1.0 - $zone->zone_coherence
            );
        }

        // Multi-narrative convergence materials
        if (count($zone->dominant_narratives) >= 3) {
            $materials[] = $this->createMaterial(
                'convergence_matrix',
                'synthesis_catalyst',
                $this->generateConvergenceContent($zone),
                0.6,
                0.8
            );
        }

        return $materials;
    }

    public function extractFromWorldEvolution(WorldState $world, array $previousState): array
    {
        $materials = [];
        $delta = $this->calculateEvolutionDelta($world, $previousState);

        // Significant coherence change
        if (abs($delta['coherence']) > 0.1) {
            $materials[] = $this->createMaterial(
                'coherence_residue',
                'stability_echo',
                $this->generateCoherenceContent($world, $delta),
                0.4,
                abs($delta['coherence'])
            );
        }

        // Entropy spike
        if ($delta['entropy'] > 0.15) {
            $materials[] = $this->createMaterial(
                'entropy_vapor',
                'chaos_manifestation',
                $this->generateEntropyContent($world, $delta),
                0.9,
                $delta['entropy']
            );
        }

        // Hybrid emergence
        if ($world->pending_hybrid) {
            $materials[] = $this->createMaterial(
                'hybrid_seed',
                'evolution_catalyst',
                $this->generateHybridContent($world),
                0.7,
                0.9
            );
        }

        return $materials;
    }

    private function extractBeliefMaterials(WorldInteraction $interaction): array
    {
        $materials = [];
        $strength = $interaction->strength;

        // Faith challenged by rationality
        $materials[] = $this->createMaterial(
            'doubt_crystal',
            'faith_fragment',
            [
                'origin' => 'Belief system challenged by external logic',
                'manifestation' => 'Crystallized doubt in formerly certain beliefs',
                'properties' => [
                    'clarity' => 0.3 + ($strength * 0.4),
                    'purity' => 0.8 - ($strength * 0.3),
                    'resonance' => $strength
                ]
            ],
            0.6,
            $strength
        );

        // Rational contaminated by faith
        $materials[] = $this->createMaterial(
            'paradox_orb',
            'logic_fragment',
            [
                'origin' => 'Logical system encountering inexplicable phenomena',
                'manifestation' => 'Orb of paradoxical truths that defy rational analysis',
                'properties' => [
                    'consistency' => 0.7 - ($strength * 0.2),
                    'complexity' => 0.4 + ($strength * 0.5),
                    'unpredictability' => $strength * 0.6
                ]
            ],
            0.7,
            $strength
        );

        return $materials;
    }

    private function extractResourceMaterials(WorldInteraction $interaction): array
    {
        $materials = [];
        $strength = $interaction->strength;

        // Resource conflict
        $materials[] = $this->createMaterial(
            'scarcity_essence',
            'conflict_catalyst',
            [
                'origin' => 'Resource competition between political systems',
                'manifestation' => 'Concentrated essence of scarcity driving conflict',
                'properties' => [
                    'potency' => $strength * 0.8,
                    'volatility' => $strength * 0.6,
                    'contagion' => $strength * 0.4
                ]
            ],
            0.8,
            $strength
        );

        // Power exchange
        $materials[] = $this->createMaterial(
            'influence_dust',
            'power_fragment',
            [
                'origin' => 'Transfer of political influence through resource control',
                'manifestation' => 'Fine dust carrying the weight of authority',
                'properties' => [
                    'density' => $strength * 0.7,
                    'magnetism' => $strength * 0.5,
                    'stability' => 0.6 + ($strength * 0.2)
                ]
            ],
            0.5,
            $strength * 0.8
        );

        return $materials;
    }

    private function extractRealityMaterials(WorldInteraction $interaction): array
    {
        $materials = [];
        $strength = $interaction->strength;

        // Reality distortion
        $materials[] = $this->createMaterial(
            'distortion_mirror',
            'reality_fragment',
            [
                'origin' => 'Boundary between different narrative realities weakening',
                'manifestation' => 'Mirror showing fractured reflections of what could be',
                'properties' => [
                    'reflection_clarity' => 0.2 + ($strength * 0.3),
                    'fracture_depth' => $strength * 0.7,
                    'possibility_leak' => $strength * 0.9
                ]
            ],
            0.9,
            $strength
        );

        // Emergent properties
        $materials[] = $this->createMaterial(
            'possibility_spark',
            'creation_seed',
            [
                'origin' => 'New possibilities emerging from reality collision',
                'manifestation' => 'Sparks of pure potential that could form new realities',
                'properties' => [
                    'creativity' => $strength * 0.8,
                    'instability' => $strength * 0.6,
                    'growth_potential' => $strength * 0.9
                ]
            ],
            0.4,
            $strength * 0.7
        );

        return $materials;
    }

    private function extractNarrativeMaterials(WorldInteraction $interaction): array
    {
        $materials = [];
        $strength = $interaction->strength;

        // Story bleed
        $materials[] = $this->createMaterial(
            'echo_ink',
            'narrative_fragment',
            [
                'origin' => 'Stories and memories leaking across world boundaries',
                'manifestation' => 'Ink that writes itself showing fragments of other narratives',
                'properties' => [
                    'readability' => 0.3 + ($strength * 0.4),
                    'contagion' => $strength * 0.5,
                    'adaptation' => $strength * 0.6
                ]
            ],
            0.5,
            $strength * 0.6
        );

        return $materials;
    }

    private function generateHarmonyContent(InteractionZone $zone): array
    {
        return [
            'origin' => 'Zone of high coherence creating stable harmonics',
            'description' => 'When multiple worlds align in perfect coherence, they create crystals of pure stability',
            'narrative_potential' => [
                'type' => 'peaceful_resolution',
                'themes' => ['unity', 'understanding', 'cooperation'],
                'conflict_resolution' => 'diplomatic synthesis'
            ],
            'world_signatures' => $zone->dominant_narratives
        ];
    }

    private function generateFragmentContent(InteractionZone $zone): array
    {
        return [
            'origin' => 'Zone collapse breaking reality into fragments',
            'description' => 'When coherence fails, reality shatters into sharp fragments of what was',
            'narrative_potential' => [
                'type' => 'reconstruction_mystery',
                'themes' => ['loss', 'memory', 'identity'],
                'conflict_resolution' => 'puzzle_assembly'
            ],
            'fragment_count' => $zone->getWorldCount(),
            'fracture_patterns' => $this->generateFracturePatterns($zone)
        ];
    }

    private function generateConvergenceContent(InteractionZone $zone): array
    {
        return [
            'origin' => 'Multiple narrative streams converging into synthesis',
            'description' => 'When different world narratives meet, they create matrices of new possibilities',
            'narrative_potential' => [
                'type' => 'emergent_synthesis',
                'themes' => ['integration', 'transformation', 'evolution'],
                'conflict_resolution' => 'hybrid_creation'
            ],
            'convergence_strength' => count($zone->dominant_narratives),
            'synthesis_pathways' => $this->generateSynthesisPathways($zone)
        ];
    }

    private function generateCoherenceContent(WorldState $world, array $delta): array
    {
        $direction = $delta['coherence'] > 0 ? 'increasing' : 'decreasing';
        
        return [
            'origin' => "World coherence {$direction} significantly",
            'description' => "Traces of stability {$direction} beyond natural evolution",
            'narrative_potential' => [
                'type' => $direction === 'increasing' ? 'stabilization_arc' : 'deconstruction_arc',
                'themes' => $direction === 'increasing' ? ['order', 'clarity', 'peace'] : ['chaos', 'confusion', 'loss'],
                'conflict_resolution' => $direction === 'increasing' ? 'restoration' : 'adaptation'
            ],
            'coherence_level' => $world->coherence,
            'change_magnitude' => abs($delta['coherence'])
        ];
    }

    private function generateEntropyContent(WorldState $world, array $delta): array
    {
        return [
            'origin' => 'Sudden entropy surge in world dynamics',
            'description' => 'Chaotic energy erupting from systemic stress',
            'narrative_potential' => [
                'type' => 'chaos_eruption',
                'themes' => ['disruption', 'unpredictability', 'transformation'],
                'conflict_resolution' => 'chaos_adaptation'
            ],
            'entropy_level' => $world->entropy,
            'eruption_intensity' => $delta['entropy']
        ];
    }

    private function generateHybridContent(WorldState $world): array
    {
        return [
            'origin' => 'World undergoing hybrid transformation',
            'description' => 'Evolutionary catalyst creating new preset from interaction',
            'narrative_potential' => [
                'type' => 'evolution_jump',
                'themes' => ['transformation', 'synthesis', 'metamorphosis'],
                'conflict_resolution' => 'integration'
            ],
            'hybrid_type' => $world->pending_hybrid['type'] ?? 'unknown',
            'transformation_progress' => 0.3 // Early stage
        ];
    }

    private function createMaterial(string $type, string $archetype, $content, float $tensionLevel, float $relevanceScore): array
    {
        return [
            'id' => uniqid('material_'),
            'type' => $type,
            'archetype' => $archetype,
            'content' => $content,
            'tension_level' => $tensionLevel,
            'relevance_score' => $relevanceScore,
            'extraction_timestamp' => time(),
            'rarity' => $this->calculateRarity($type, $relevanceScore),
            'story_potential' => $this->calculateStoryPotential($type, $tensionLevel)
        ];
    }

    private function calculateEvolutionDelta(WorldState $world, array $previousState): array
    {
        return [
            'coherence' => $world->coherence - ($previousState['coherence'] ?? 0),
            'entropy' => $world->entropy - ($previousState['entropy'] ?? 0),
            'stability' => $world->stability - ($previousState['stability'] ?? 0),
            'dominance' => $world->dominance_level - ($previousState['dominance_level'] ?? 0)
        ];
    }

    private function calculateRarity(string $type, float $relevanceScore): string
    {
        $baseRarity = $this->materialTemplates[$type]['base_rarity'] ?? 'common';
        
        if ($relevanceScore > 0.8) return 'legendary';
        if ($relevanceScore > 0.6) return 'epic';
        if ($relevanceScore > 0.4) return 'rare';
        if ($relevanceScore > 0.2) return 'uncommon';
        
        return $baseRarity;
    }

    private function calculateStoryPotential(string $type, float $tensionLevel): float
    {
        $basePotential = $this->materialTemplates[$type]['story_potential'] ?? 0.5;
        $tensionBonus = $tensionLevel * 0.3;
        
        return min(1.0, $basePotential + $tensionBonus);
    }

    private function generateFracturePatterns(InteractionZone $zone): array
    {
        return [
            'radial_fractures' => rand(3, 8),
            'spiral_patterns' => rand(1, 3),
            'depth_layers' => rand(2, 5),
            'resonance_frequencies' => array_map(fn() => rand(100, 999), range(1, 5))
        ];
    }

    private function generateSynthesisPathways(InteractionZone $zone): array
    {
        $pathways = [];
        $narratives = $zone->dominant_narratives;
        
        for ($i = 0; $i < count($narratives); $i++) {
            for ($j = $i + 1; $j < count($narratives); $j++) {
                $pathways[] = [
                    'from' => $narratives[$i],
                    'to' => $narratives[$j],
                    'synthesis_type' => $this->determineSynthesisType($narratives[$i], $narratives[$j]),
                    'efficiency' => rand(60, 95) / 100
                ];
            }
        }
        
        return $pathways;
    }

    private function determineSynthesisType(string $narrativeA, string $narrativeB): string
    {
        $synthesisMap = [
            'faith-rational' => 'scientific_religion',
            'political-resource' => 'resource_politics',
            'chaotic-stable' => 'controlled_chaos',
            'faith-political' => 'theocratic_governance',
            'rational-resource' => 'logical_economics'
        ];
        
        $key = min($narrativeA, $narrativeB) . '-' . max($narrativeA, $narrativeB);
        return $synthesisMap[$key] ?? 'generic_synthesis';
    }

    private function initializeExtractionPatterns(): void
    {
        $this->extractionPatterns = [
            'BELIEF_CONTAMINATION' => [
                'materials' => ['doubt_crystal', 'paradox_orb', 'faith_fragment', 'logic_fragment'],
                'triggers' => ['coherence_conflict', 'belief_challenge', 'rational_encounter']
            ],
            'RESOURCE_CROSSFLOW' => [
                'materials' => ['scarcity_essence', 'influence_dust', 'conflict_catalyst', 'power_fragment'],
                'triggers' => ['resource_competition', 'political_control', 'economic_war']
            ],
            'REALITY_DISTORTION' => [
                'materials' => ['distortion_mirror', 'possibility_spark', 'reality_fragment', 'creation_seed'],
                'triggers' => ['boundary_weakness', 'narrative_collision', 'physics_conflict']
            ],
            'NARRATIVE_BLEED' => [
                'materials' => ['echo_ink', 'memory_fragment', 'story_leak', 'narrative_residue'],
                'triggers' => ['world_proximity', 'story_crossing', 'memory_bleed']
            ]
        ];
    }

    private function initializeMaterialTemplates(): void
    {
        $this->materialTemplates = [
            'doubt_crystal' => ['base_rarity' => 'rare', 'story_potential' => 0.8],
            'paradox_orb' => ['base_rarity' => 'epic', 'story_potential' => 0.9],
            'scarcity_essence' => ['base_rarity' => 'uncommon', 'story_potential' => 0.6],
            'distortion_mirror' => ['base_rarity' => 'legendary', 'story_potential' => 0.95],
            'harmony_crystal' => ['base_rarity' => 'rare', 'story_potential' => 0.7],
            'fragment_shard' => ['base_rarity' => 'common', 'story_potential' => 0.4],
            'convergence_matrix' => ['base_rarity' => 'epic', 'story_potential' => 0.85],
            'hybrid_seed' => ['base_rarity' => 'legendary', 'story_potential' => 1.0]
        ];
    }
}
