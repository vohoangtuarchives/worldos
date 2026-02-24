<?php

namespace App\CivilizationForge;

use App\Models\WorldState;
use App\Models\MaterialSeed;
use App\Models\StoryArc;
use Illuminate\Support\Collection;

class EvolutionService
{
    public function evolveAxes(WorldState $world): void
    {
        $world->evolveAxes();
    }

    public function applyAuthorIntent(WorldState $world, array $intent): void
    {
        $world->applyIntent($intent);
    }

    public function generateResistance(WorldState $world): array
    {
        $resistanceFactor = $world->resistance_factor ?? 0.15;
        
        return [
            'unpredictable_events' => $this->generateUnpredictableEvents($resistanceFactor),
            'system_inertia' => $this->calculateSystemInertia($world),
            'emergent_complexity' => $this->createEmergentComplexity($world, $resistanceFactor)
        ];
    }

    public function calculatePressurePoints(WorldState $world): Collection
    {
        return collect($world->calculatePressurePoints());
    }

    /**
     * Generate initial world state from author intent
     */
    public function initializeWorld(array $authorIntent, string $structuralAnchor, ?string $preset = null): WorldState
    {
        $world = WorldState::create([
            'power_axis' => $this->initializePowerAxis($authorIntent),
            'resource_axis' => $this->initializeResourceAxis($authorIntent),
            'perception_axis' => $this->initializePerceptionAxis($authorIntent),
            'author_intent' => $authorIntent,
            'structural_anchor' => $structuralAnchor,
            'current_preset' => $preset ?? 'stable',
            'resistance_factor' => 0.15
        ]);

        return $world;
    }

    /**
     * Evolve world and generate materials
     */
    public function evolveAndGenerate(WorldState $world): array
    {
        // Evolve the axes
        $this->evolveAxes($world);

        // Calculate pressure points
        $pressurePoints = $this->calculatePressurePoints($world);

        // Generate materials from pressure points
        $materials = $this->generateMaterialsFromPressure($world, $pressurePoints);

        // Generate resistance elements
        $resistance = $this->generateResistance($world);

        return [
            'pressure_points' => $pressurePoints->toArray(),
            'materials' => $materials,
            'resistance' => $resistance,
            'world_state' => $world->fresh()
        ];
    }

    private function initializePowerAxis(array $intent): array
    {
        $gradient = $intent['power_gradient'] ?? 'medium';
        
        return match($gradient) {
            'steep' => [
                'central_authority' => 0.8,
                'faction_power' => 0.3,
                'individual_influence' => 0.2,
                'institutional_control' => 0.9
            ],
            'gentle' => [
                'central_authority' => 0.4,
                'faction_power' => 0.5,
                'individual_influence' => 0.6,
                'institutional_control' => 0.3
            ],
            default => [
                'central_authority' => 0.6,
                'faction_power' => 0.5,
                'individual_influence' => 0.4,
                'institutional_control' => 0.6
            ]
        };
    }

    private function initializeResourceAxis(array $intent): array
    {
        $density = $intent['resource_density'] ?? 'medium';
        
        return match($density) {
            'scarce' => [
                'primary_resource' => 0.2,
                'alternative_resources' => 0.3,
                'resource_distribution' => 0.1,
                'access_control' => 0.8
            ],
            'abundant' => [
                'primary_resource' => 0.9,
                'alternative_resources' => 0.8,
                'resource_distribution' => 0.7,
                'access_control' => 0.3
            ],
            default => [
                'primary_resource' => 0.6,
                'alternative_resources' => 0.5,
                'resource_distribution' => 0.4,
                'access_control' => 0.5
            ]
        };
    }

    private function initializePerceptionAxis(array $intent): array
    {
        $complexity = $intent['perception_complexity'] ?? 'medium';
        
        return match($complexity) {
            'simple' => [
                'dominant_belief' => 0.9,
                'alternative_beliefs' => 0.1,
                'belief_uniformity' => 0.8,
                'ideological_control' => 0.7
            ],
            'complex' => [
                'dominant_belief' => 0.4,
                'alternative_beliefs' => 0.7,
                'belief_uniformity' => 0.2,
                'ideological_control' => 0.3
            ],
            default => [
                'dominant_belief' => 0.6,
                'alternative_beliefs' => 0.4,
                'belief_uniformity' => 0.5,
                'ideological_control' => 0.5
            ]
        };
    }

    private function generateUnpredictableEvents(float $factor): array
    {
        if (rand(0, 100) < ($factor * 100)) {
            $events = [
                'natural_disaster' => 'Earthquake or flood disrupts power structures',
                'external_contact' => 'Unknown group arrives with different customs',
                'discovery' => 'Ancient technology or knowledge unearthed',
                'betrayal' => 'Trusted ally reveals hidden agenda',
                'miracle' => 'Unexplained event challenges belief systems'
            ];

            return [
                'event' => array_rand($events),
                'description' => $events[array_rand($events)],
                'impact_level' => $factor * 2
            ];
        }

        return ['event' => null, 'description' => 'No unpredictable events'];
    }

    private function calculateSystemInertia(WorldState $world): float
    {
        // Inertia based on how established the systems are
        $powerInertia = array_sum($world->power_axis) / count($world->power_axis);
        $resourceInertia = array_sum($world->resource_axis) / count($world->resource_axis);
        $perceptionInertia = array_sum($world->perception_axis) / count($world->perception_axis);

        return ($powerInertia + $resourceInertia + $perceptionInertia) / 3;
    }

    private function createEmergentComplexity(WorldState $world, float $factor): array
    {
        $complexity = [];
        
        // Generate emergent behaviors from system interactions
        if ($world->power_axis['central_authority'] > 0.7 && $world->resource_axis['resource_distribution'] < 0.3) {
            $complexity[] = 'Black markets form to bypass resource controls';
        }

        if ($world->perception_axis['alternative_beliefs'] > 0.6 && $world->power_axis['institutional_control'] > 0.7) {
            $complexity[] = 'Underground movements challenge ideological control';
        }

        if ($world->resource_axis['primary_resource'] < 0.3 && $world->power_axis['faction_power'] > 0.6) {
            $complexity[] = 'Resource wars between competing factions';
        }

        return $complexity;
    }

    private function generateMaterialsFromPressure(WorldState $world, Collection $pressurePoints): array
    {
        $materials = [];

        // Generate conflict seeds from pressure points
        foreach ($pressurePoints as $pressure) {
            $seed = MaterialSeed::fromAxisCollision($world, $pressure);
            $seed->save();
            $materials[] = $seed;
        }

        // Generate character seed from structural anchor
        $characterSeed = MaterialSeed::fromStructuralAnchor($world, $world->structural_anchor);
        $characterSeed->save();
        $materials[] = $characterSeed;

        return $materials;
    }

    /**
     * Create story arc from generated materials
     */
    public function createStoryArc(WorldState $world, array $materialIds): StoryArc
    {
        return StoryArc::fromMaterialSeeds($world, $materialIds);
    }

    /**
     * Generate complete story package
     */
    public function generateStoryPackage(array $authorIntent, string $structuralAnchor, ?string $preset = null): array
    {
        // Initialize world
        $world = $this->initializeWorld($authorIntent, $structuralAnchor, $preset);

        // Evolve and generate materials
        $evolution = $this->evolveAndGenerate($world);

        // Create story arc
        $materialIds = array_map(fn($material) => $material->id, $evolution['materials']);
        $storyArc = $this->createStoryArc($world, $materialIds);
        $storyArc->save();

        return [
            'world_state' => $world,
            'evolution' => $evolution,
            'story_arc' => $storyArc,
            'package_summary' => [
                'total_materials' => count($evolution['materials']),
                'pressure_points' => count($evolution['pressure_points']),
                'estimated_chapters' => $storyArc->estimated_chapters,
                'tension_progression' => $storyArc->tension_progression,
                'resistance_events' => $evolution['resistance']['unpredictable_events']['event'] ? 1 : 0
            ]
        ];
    }
}
