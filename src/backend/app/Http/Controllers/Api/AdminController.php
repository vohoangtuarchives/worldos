<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use Illuminate\Http\JsonResponse;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_universes' => UniverseModel::count(),
            'active_universes' => UniverseModel::where('is_archived', false)->count(),
            'archived_universes' => UniverseModel::where('is_archived', true)->count(),
            // innovative metric: total 'energy' or 'complexity' across all universes?
            'total_complexity' => DB::table('universes')->sum('entropy') ?? 0,
        ]);
    }


    public function evolutionOverview(): JsonResponse
    {
        $now = now();
        $lastHour = $now->copy()->subHour();

        $generationsLastHour = DB::table('ai_generations')
            ->where('created_at', '>=', $lastHour)
            ->count();

        $totalGenerations = DB::table('ai_generations')->count();
        $failedGenerations = DB::table('ai_generations')
            ->whereIn('status', ['FAILED', 'REJECTED'])
            ->count();

        $collapseRate = $totalGenerations > 0
            ? round(($failedGenerations / $totalGenerations) * 100, 2)
            : 0.0;

        return response()->json([
            'success' => true,
            'data' => [
                'generations_per_hour' => $generationsLastHour,
                'collapse_rate_percent' => $collapseRate,
                'frontier_size' => DB::table('universes')->where('is_archived', false)->count(),
                'ai_enabled' => (bool) Cache::get('admin:ai_enabled', (bool) config('ai.enabled', false)),
                'updated_at' => $now->toISOString(),
            ],
        ]);
    }

    public function toggleAI(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        Cache::forever('admin:ai_enabled', (bool) $validated['enabled']);

        return response()->json([
            'success' => true,
            'data' => [
                'ai_enabled' => (bool) $validated['enabled'],
            ],
            'message' => $validated['enabled'] ? 'AI enabled.' : 'AI disabled.',
        ]);
    }

    public function universes(): JsonResponse
    {
        // Simple listing for admin dashboard
        return response()->json(
            UniverseModel::select('id', 'name', 'status', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function toggleLock(string $id): JsonResponse
    {
        $universe = UniverseModel::find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }
        // Assuming 'locked' logic or just state toggle
        // For now, let's just toggle state between active/paused if applicable, or log it.
        // Or if there is a 'is_locked' column. 
        // Let's assume it's a state change for now.
        
        return response()->json(['message' => "Universe {$id} lock toggled (stub)"]);
    }
}
