<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\JsonResponse;

/**
 * Cluster API: snapshot aggregation for control plane (~100 worlds).
 * GET /api/cluster/snapshot — one request for dashboard; poll every 10–15s.
 */
class ClusterController extends Controller
{
    /**
     * GET /api/cluster/snapshot
     * Returns aggregated world list + cluster stats for Cluster dashboard.
     */
    public function snapshot(): JsonResponse
    {
        $worlds = World::with('state')
            ->select([
                'id', 'name', 'health_status', 'status', 'current_tick', 'preset', 'genre',
                'cosmic_entropy', 'cosmic_stability', 'cosmic_energy',
                'updated_at',
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        $runningStatuses = ['active', 'ACTIVE', 'running', 'RUNNING', ''];
        $running = $worlds->filter(function (World $w) use ($runningStatuses) {
            $s = $w->status;
            if ($s === null) {
                return true;
            }
            return in_array((string) $s, $runningStatuses, true);
        })->count();

        $items = $worlds->map(function (World $w) {
            $stateVector = $w->state?->state_vector ?? [];
            $entropy = $w->cosmic_entropy
                ?? (is_array($stateVector) ? ($stateVector['entropy'] ?? null) : null);
            $stability = $w->cosmic_stability
                ?? (is_array($stateVector) ? ($stateVector['stability'] ?? null) : null);

            return [
                'id' => (string) $w->id,
                'name' => $w->name,
                'status' => $w->status,
                'health_status' => $w->health_status instanceof \BackedEnum ? $w->health_status->value : $w->health_status,
                'current_tick' => (int) ($w->current_tick ?? 0),
                'entropy' => $entropy !== null ? (float) $entropy : null,
                'stability' => $stability !== null ? (float) $stability : null,
                'preset' => $w->preset,
                'genre' => $w->genre,
                'updated_at' => $w->updated_at?->format(\DateTimeInterface::ATOM),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'worlds' => $items->values()->all(),
                'clusterStats' => [
                    'total' => $worlds->count(),
                    'running' => $running,
                ],
            ],
        ]);
    }

    /**
     * GET /api/cluster/governor
     * Governor state (pressure, throttle). Stub until real governor service is wired.
     */
    public function governor(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pressureScore' => 0.0,
                'throttleLevel' => 'normal',
                'emergencyMode' => false,
                'costBurnRate' => null,
            ],
        ]);
    }

    /**
     * GET /api/cluster/system
     * System metrics (CPU, memory, queue). Stub until metrics collector is wired.
     */
    public function system(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'cpuPercent' => null,
                'memoryPercent' => null,
                'queueLength' => null,
            ],
        ]);
    }

    /**
     * POST /api/cluster/emergency-freeze
     * Pause all worlds / disable escalation. Stub; later calls Governor service.
     */
    public function emergencyFreeze(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Emergency freeze requested (stub). Wire to Governor to pause cluster.',
        ]);
    }
}
