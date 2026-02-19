<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $universe = UniverseModel::findOrFail($id);
        // Assuming 'locked' logic or just state toggle
        // For now, let's just toggle state between active/paused if applicable, or log it.
        // Or if there is a 'is_locked' column. 
        // Let's assume it's a state change for now.
        
        return response()->json(['message' => "Universe {$id} lock toggled (stub)"]);
    }
}
