<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Models\WorldHealthSnapshot;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index()
    {
        // Get all worlds with their latest snapshot
        $worlds = World::with(['clock'])
            ->get()
            ->map(function ($world) {
                $latestSnapshot = WorldHealthSnapshot::where('world_id', $world->id)
                    ->latest('recorded_at')
                    ->first();
                
                return [
                    'id' => $world->id,
                    'name' => $world->name,
                    'current_health' => $world->health_status,
                    'last_snapshot' => $latestSnapshot?->recorded_at,
                    'snapshot_count' => WorldHealthSnapshot::where('world_id', $world->id)->count(),
                ];
            });

        return view('admin.wmcp.health.index', compact('worlds'));
    }

    public function show($worldId)
    {
        $world = World::with('clock')->find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        
        // Get historical snapshots (last 30 days or 100 records)
        $snapshots = WorldHealthSnapshot::where('world_id', $worldId)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Prepare chart data
        $chartData = [
            'labels' => $snapshots->pluck('recorded_at')->map(fn($d) => $d->format('M d H:i'))->toArray(),
            'data' => $snapshots->map(function ($snapshot) {
                // Convert health status to numeric score for chart
                return match($snapshot->health_status) {
                    \App\Domains\World\Enums\WorldHealthStatus::STABLE => 100,
                    \App\Domains\World\Enums\WorldHealthStatus::DEGRADED => 60,
                    \App\Domains\World\Enums\WorldHealthStatus::CRITICAL => 20,
                    \App\Domains\World\Enums\WorldHealthStatus::HALTED => 0,
                };
            })->toArray(),
        ];

        return view('admin.wmcp.health.show', compact('world', 'snapshots', 'chartData'));
    }
}
