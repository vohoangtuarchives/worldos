<?php

namespace App\Http\Controllers\Api;

use App\Domains\Vietnamese\Models\VietnameseHero;
use App\Domains\Vietnamese\Models\HeroEvent;
use App\Domains\Vietnamese\Services\HeroScoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class VietnameseHeroController extends Controller
{
    public function __construct(
        private HeroScoringService $scoringService
    ) {}

    /**
     * List heroes with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = VietnameseHero::query();

        // Filters
        if ($period = $request->input('period')) {
            $query->where('period', $period);
        }

        if ($era = $request->input('era')) {
            $query->where('era', $era);
        }

        if ($archetype = $request->input('archetype')) {
            $query->where('archetype', $archetype);
        }

        // Dimension filter (e.g., ?strong_in=military&threshold=0.8)
        if ($dimension = $request->input('strong_in')) {
            $threshold = $request->input('threshold', 0.7);
            $query->where($dimension, '>=', $threshold);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'impact_score');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->input('per_page', 20), 100);
        $heroes = $query->paginate($perPage);

        return response()->json($heroes);
    }

    /**
     * Get single hero with events
     */
    public function show(string $id): JsonResponse
    {
        $hero = VietnameseHero::with('events')->findOrFail($id);

        return response()->json([
            'hero' => $hero,
            'dimensions' => $hero->dimensions,
            'top_dimensions' => $hero->topDimensions,
            'events_count' => $hero->events->count(),
        ]);
    }

    /**
     * Get top heroes by dimension
     */
    public function topByDimension(string $dimension, Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 10), 50);

        $heroes = $this->scoringService->rankByDimension($dimension, $limit);

        return response()->json([
            'dimension' => $dimension,
            'top_heroes' => $heroes,
        ]);
    }

    /**
     * Get era profile (average dimensions)
     */
    public function eraProfile(int $era): JsonResponse
    {
        $profile = $this->scoringService->getEraProfile($era);
        $heroes = VietnameseHero::where('era', $era)->get(['id', 'name', 'impact_score']);

        return response()->json([
            'era' => $era,
            'profile' => $profile,
            'heroes' => $heroes,
        ]);
    }

    /**
     * Search heroes by name
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        $heroes = VietnameseHero::where('name', 'ILIKE', "%{$query}%")
            ->orderBy('impact_score', 'desc')
            ->limit(20)
            ->get();

        return response()->json($heroes);
    }

    /**
     * Get hero events
     */
    public function events(string $id): JsonResponse
    {
        $hero = VietnameseHero::findOrFail($id);
        $events = $hero->events()->orderBy('year')->get();

        return response()->json([
            'hero' => $hero->only(['id', 'name']),
            'events' => $events,
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'total_heroes' => VietnameseHero::count(),
            'total_events' => HeroEvent::count(),
            'by_period' => VietnameseHero::selectRaw('period, count(*) as count')
                ->groupBy('period')
                ->get(),
            'by_era' => VietnameseHero::selectRaw('era, count(*) as count')
                ->whereNotNull('era')
                ->groupBy('era')
                ->orderBy('era')
                ->get(),
            'top_impact' => VietnameseHero::orderBy('impact_score', 'desc')
                ->limit(5)
                ->get(['name', 'impact_score', 'period']),
        ]);
    }

    /**
     * Get dimension distribution
     */
    public function dimensionDistribution(): JsonResponse
    {
        $dimensions = ['military', 'governance', 'territory', 'philosophy', 
                       'education', 'culture', 'spirituality', 'rebellion',
                       'reform', 'diplomacy', 'economic', 'mythic'];

        $distribution = [];

        foreach ($dimensions as $dim) {
            $distribution[$dim] = [
                'average' => VietnameseHero::avg($dim),
                'max' => VietnameseHero::max($dim),
                'top_hero' => VietnameseHero::orderBy($dim, 'desc')
                    ->first(['name', $dim]),
            ];
        }

        return response()->json($distribution);
    }
}
