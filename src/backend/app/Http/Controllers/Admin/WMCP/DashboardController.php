<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Models\WorldAlert;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use App\Models\WorldSeed;
use App\Models\WorldMyth;
use App\Models\WorldScar;
use App\Models\WorldEvent;
use App\Models\WorldPrimitive;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Operator Playbook: Group by Health Status for Immediate Action
        $criticalWorlds = World::where('health_status', \App\Domains\World\Enums\WorldHealthStatus::CRITICAL)
            ->with(['events' => function($q) { $q->latest()->limit(1); }]) // Get top event/alert reason
            ->get();

        $degradedWorlds = World::where('health_status', \App\Domains\World\Enums\WorldHealthStatus::DEGRADED)
            ->with(['events' => function($q) { $q->latest()->limit(1); }])
            ->get();

        $stableWorlds = World::where('health_status', \App\Domains\World\Enums\WorldHealthStatus::STABLE)
            ->with(['events' => function($q) { $q->latest()->limit(1); }])
            ->get();
            
        $haltedWorlds = World::where('health_status', \App\Domains\World\Enums\WorldHealthStatus::HALTED)
            ->with(['events' => function($q) { $q->latest()->limit(1); }])
            ->get();
        
        $activeAlerts = \App\Models\WorldAlert::with('world')
            ->where('resolved', false)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.wmcp.dashboard', compact(
            'criticalWorlds', 
            'degradedWorlds', 
            'stableWorlds', 
            'haltedWorlds',
            'activeAlerts'
        ));
    }

    /**
     * World Factors Overview - All factors affecting world operation
     */
    public function worldFactors($worldId)
    {
        $world = World::with('clock')->find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }

        // Foundation: Primitives bound to this world
        $primitives = WorldPrimitive::whereHas('worldBindings', function($q) use ($worldId) {
            $q->where('world_id', $worldId);
        })->get()->groupBy('domain');

        // Input: Active seeds
        $seeds = WorldSeed::where('world_id', $worldId)
            ->with('template')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Emergent: Myths
        $myths = WorldMyth::where('world_id', $worldId)
            ->orderByDesc('strength')
            ->limit(10)
            ->get();

        // Consequences: Scars
        $scars = WorldScar::where('world_id', $worldId)
            ->with('sourceEvent')
            ->orderByDesc('weight')
            ->limit(10)
            ->get();

        // Events timeline
        $recentEvents = WorldEvent::where('world_id', $worldId)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // Active alerts
        $alerts = WorldAlert::where('world_id', $worldId)
            ->where('resolved', false)
            ->orderByDesc('created_at')
            ->get();

        // Stats
        $stats = [
            'total_seeds' => WorldSeed::where('world_id', $worldId)->count(),
            'active_seeds' => WorldSeed::where('world_id', $worldId)->where('state', 'ACTIVE')->count(),
            'total_myths' => WorldMyth::where('world_id', $worldId)->count(),
            'active_myths' => WorldMyth::where('world_id', $worldId)->where('status', 'active')->count(),
            'total_scars' => WorldScar::where('world_id', $worldId)->count(),
            'total_events' => WorldEvent::where('world_id', $worldId)->count(),
            'unresolved_alerts' => $alerts->count(),
        ];

        return view('admin.wmcp.world_factors', compact(
            'world',
            'primitives',
            'seeds',
            'myths',
            'scars',
            'recentEvents',
            'alerts',
            'stats'
        ));
    }
}
