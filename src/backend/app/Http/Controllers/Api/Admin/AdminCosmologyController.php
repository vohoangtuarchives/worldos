<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use App\Models\WorldAlert;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use App\Models\GovernanceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCosmologyController extends Controller
{
    /**
     * Get global administrative statistics.
     */
    public function getStats()
    {
        $stats = [
            'total_universes' => UniverseModel::count(),
            'active_universes' => UniverseModel::where('is_archived', false)->count(),
            'archived_universes' => UniverseModel::where('is_archived', true)->count(),
            'unresolved_alerts' => WorldAlert::where('resolved', false)->count(),
            'health_distribution' => [
                'stable' => UniverseModel::where('is_archived', false)->where('state_vector->entropy', '<', 0.5)->count(),
                'degraded' => UniverseModel::where('is_archived', false)->whereBetween('state_vector->entropy', [0.5, 0.8])->count(),
                'critical' => UniverseModel::where('is_archived', false)->where('state_vector->entropy', '>', 0.8)->count(),
            ]
        ];

        return response()->json($stats);
    }

    /**
     * List all universes with management metadata.
     */
    public function index()
    {
        $universes = UniverseModel::with('cosmicFaction')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($universes);
    }

    /**
     * Toggle Lock/Unlock on a universe.
     */
    public function toggleLock($id)
    {
        $u = UniverseModel::find($id);
        if (!$u) {
            throw UniverseNotFoundException::withId((string) $id);
        }
        // Assuming we add a 'is_locked' column or use state
        // For now, let's just log it as a simulation of the action if column doesn't exist
        // or toggle a temporary status in parameters.
        
        $params = $u->parameters ?? [];
        $params['is_locked'] = !($params['is_locked'] ?? false);
        $u->parameters = $params;
        $u->save();

        GovernanceAuditLog::create([
            'universe_id' => $u->id,
            'action' => $params['is_locked'] ? 'ADMIN_LOCK' : 'ADMIN_UNLOCK',
            'details' => json_encode(['operator' => 'Architect']),
            'severity' => 'WARNING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Lock toggled', 'is_locked' => $params['is_locked']]);
    }

    /**
     * Get recent audit logs.
     */
    public function getAuditLogs()
    {
        $logs = GovernanceAuditLog::latest()->limit(50)->get();
        return response()->json($logs);
    }

    /**
     * Get active alerts.
     */
    public function getAlerts()
    {
        $alerts = WorldAlert::with('world')
            ->where('resolved', false)
            ->latest()
            ->get();
        return response()->json($alerts);
    }
}
