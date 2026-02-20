<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Domains\Material\Analytics\MaterialAnalytics;
use App\Models\World;
use Illuminate\Http\Request;
use Tuzy\Domain\World\Exception\WorldNotFoundException;

class MaterialAnalyticsController extends Controller
{
    private MaterialAnalytics $analytics;

    public function __construct(MaterialAnalytics $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Show analytics dashboard for a world.
     */
    public function index(Request $request)
    {
        $worldId = $request->query('world_id');
        
        if (!$worldId) {
            return view('admin.wmcp.materials.analytics', [
                'world' => null,
                'analytics' => null,
            ]);
        }

        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        $analytics = $this->analytics->getWorldAnalytics($world);

        return view('admin.wmcp.materials.analytics', [
            'world' => $world,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Get analytics data as JSON for AJAX requests.
     */
    public function data(Request $request)
    {
        $worldId = $request->query('world_id');
        
        if (!$worldId) {
            return response()->json(['error' => 'world_id required'], 400);
        }

        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        $analytics = $this->analytics->getWorldAnalytics($world);

        return response()->json($analytics);
    }
}
