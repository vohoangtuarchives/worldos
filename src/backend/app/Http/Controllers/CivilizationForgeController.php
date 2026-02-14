<?php

namespace App\Http\Controllers;

use App\CivilizationForge\EvolutionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CivilizationForgeController extends Controller
{
    public function __construct(private EvolutionService $evolutionService)
    {
    }

    /**
     * Display Civilization Forge interface
     */
    public function index()
    {
        return view('civilization-forge.index');
    }

    /**
     * Generate story package from frontend request
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $intent = $request->input('authorIntent', []);
            $anchor = $request->input('structuralAnchor', 'academic_system');

            // Validate intent
            $validIntents = [
                'narrative_density' => ['low', 'medium', 'high'],
                'power_gradient' => ['gentle', 'medium', 'steep'],
                'resource_density' => ['scarce', 'medium', 'abundant'],
                'perception_complexity' => ['simple', 'medium', 'complex'],
                'conflict_intensity' => ['low', 'medium', 'high'],
                'social_thickness' => ['light', 'medium', 'deep'],
                'mythology_layer' => ['absent', 'subtle', 'present']
            ];

            foreach ($intent as $key => $value) {
                if (isset($validIntents[$key]) && !in_array($value, $validIntents[$key])) {
                    return response()->json([
                        'error' => "Invalid value for {$key}: {$value}"
                    ], 400);
                }
            }

            $package = $this->evolutionService->generateStoryPackage($intent, $anchor);

            return response()->json([
                'success' => true,
                'package' => [
                    'world_state' => [
                        'id' => $package['world_state']->id,
                        'structural_anchor' => $package['world_state']->structural_anchor,
                        'resistance_factor' => $package['world_state']->resistance_factor,
                        'power_axis' => $package['world_state']->power_axis,
                        'resource_axis' => $package['world_state']->resource_axis,
                        'perception_axis' => $package['world_state']->perception_axis,
                        'author_intent' => $package['world_state']->author_intent
                    ],
                    'story_arc' => [
                        'id' => $package['story_arc']->id,
                        'title' => $package['story_arc']->title,
                        'arc_type' => $package['story_arc']->arc_type,
                        'estimated_chapters' => $package['story_arc']->estimated_chapters,
                        'tension_progression' => $package['story_arc']->tension_progression,
                        'content' => $package['story_arc']->content
                    ],
                    'materials' => collect($package['evolution']['materials'])->map(function ($material) {
                        return [
                            'id' => $material->id,
                            'type' => $material->seed_type,
                            'archetype' => $material->archetype,
                            'tension_level' => $material->tension_level,
                            'relevance_score' => $material->relevance_score,
                            'content' => $material->content
                        ];
                    })->toArray(),
                    'pressure_points' => $package['evolution']['pressure_points'],
                    'resistance' => $package['evolution']['resistance'],
                    'summary' => $package['package_summary']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available structural anchors
     */
    public function anchors(): JsonResponse
    {
        $anchors = [
            'academic_system' => [
                'name' => 'Academic System',
                'description' => 'Schools, universities, learning institutions',
                'examples' => ['Harry Potter magic school', 'University settings', 'Training academies']
            ],
            'faction_system' => [
                'name' => 'Faction System',
                'description' => 'Political factions, guilds, organizations',
                'examples' => ['Game of Thrones houses', 'D&D factions', 'Political parties']
            ],
            'commercial_system' => [
                'name' => 'Commercial System',
                'description' => 'Trade, commerce, merchant networks',
                'examples' => ['Trading companies', 'Market economies', 'Merchant guilds']
            ]
        ];

        return response()->json($anchors);
    }

    /**
     * Get story packages history
     */
    public function history(): JsonResponse
    {
        $packages = \App\Models\StoryArc::with('worldState')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($arc) {
                return [
                    'id' => $arc->id,
                    'title' => $arc->title,
                    'arc_type' => $arc->arc_type,
                    'estimated_chapters' => $arc->estimated_chapters,
                    'structural_anchor' => $arc->worldState->structural_anchor,
                    'created_at' => $arc->created_at->format('Y-m-d H:i:s'),
                    'materials_count' => $arc->worldState->materialSeeds()->count()
                ];
            });

        return response()->json($packages);
    }

    /**
     * Get detailed story package
     */
    public function detail(int $id): JsonResponse
    {
        $arc = \App\Models\StoryArc::with(['worldState.materialSeeds'])->findOrFail($id);

        return response()->json([
            'story_arc' => [
                'id' => $arc->id,
                'title' => $arc->title,
                'arc_type' => $arc->arc_type,
                'estimated_chapters' => $arc->estimated_chapters,
                'tension_progression' => $arc->tension_progression,
                'content' => $arc->content,
                'structure' => $arc->structure,
                'created_at' => $arc->created_at->format('Y-m-d H:i:s')
            ],
            'world_state' => [
                'id' => $arc->worldState->id,
                'structural_anchor' => $arc->worldState->structural_anchor,
                'resistance_factor' => $arc->worldState->resistance_factor,
                'power_axis' => $arc->worldState->power_axis,
                'resource_axis' => $arc->worldState->resource_axis,
                'perception_axis' => $arc->worldState->perception_axis,
                'author_intent' => $arc->worldState->author_intent
            ],
            'materials' => $arc->worldState->materialSeeds->map(function ($seed) {
                return [
                    'id' => $seed->id,
                    'type' => $seed->seed_type,
                    'archetype' => $seed->archetype,
                    'tension_level' => $seed->tension_level,
                    'relevance_score' => $seed->relevance_score,
                    'content' => $seed->content,
                    'source_axes' => $seed->source_axes
                ];
            })
        ]);
    }
}
