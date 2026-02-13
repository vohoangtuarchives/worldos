<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WMCP;

use App\Domains\Cosmic\Services\AlertEvaluationEngine;
use App\Domains\Cosmic\Services\EmergencyInterventionService;
use App\Domains\Cosmic\Services\EpochControlService;
use App\Domains\Cosmic\Services\MetricsSnapshotService;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * GodConsoleController — God Console for cosmic observation, monitoring, and control.
 *
 * Endpoints:
 *   GET  /                 → Full dashboard Blade view
 *   GET  /metrics          → Current MetricsSnapshot (18 KPIs) JSON
 *   GET  /trajectory       → Recent trajectory (N snapshots) JSON
 *   GET  /attractors       → All attractor aggregates JSON
 *   GET  /alerts           → Active alerts JSON
 *   POST /alerts/{id}/ack  → Acknowledge alert
 *   POST /control/freeze   → Freeze simulation
 *   POST /control/resume   → Resume simulation
 *   POST /control/step     → Step 1 epoch
 *   POST /control/rollback → Rollback 1 epoch
 *   POST /emergency/{act}  → Emergency intervention
 */
class GodConsoleController extends Controller
{
    public function __construct(
        private MetricsSnapshotService $metricsService,
        private AlertEvaluationEngine $alertEngine,
        private EpochControlService $epochControl,
        private EmergencyInterventionService $emergencyService,
    ) {}

    /**
     * GET /admin/wmcp/god-console/{worldId}
     * Main God Console Blade view.
     */
    public function index(int $worldId): View
    {
        $world = World::findOrFail($worldId);
        $snapshot = $this->getLatestSnapshot($world);

        // Defaults for empty world
        $emptyMetrics = [
            'stability' => ['ssi' => 0, 'cf' => 0, 'stability_margin' => 0],
            'evolution' => ['di' => 0, 'agr' => 0, 'res' => 0],
            'power' => ['icr' => 0, 'vps' => 0, 'api' => 0],
            'memory' => ['hbr' => 0, 'cm' => 0, 'mde' => 0],
            'emergence' => ['epi' => 0, 'atr' => 0],
            'governance' => ['par' => 0, 'gl' => 0, 'hii' => 0],
            'meta' => ['chs' => 0],
            'severity' => 'HEALTHY',
            'critical_four' => ['SSI' => 0, 'DI' => 0, 'CF' => 0, 'HBR' => 0],
        ];

        if (!$snapshot) {
            return view('admin.wmcp.god-console.index', [
                'world' => $world,
                'currentEpoch' => 0,
                'metrics' => $emptyMetrics,
                'alerts' => [],
                'composites' => [],
                'attractors' => [],
                'stateVector' => [0, 0, 0, 0, 0],
                'trajectoryLabels' => [],
                'trajectoryEntropy' => [],
                'trajectoryStability' => [],
                'trajectoryStrain' => [],
                'trajectoryEnergy' => [],
                'trajectoryData' => [],
            ]);
        }

        // Compute metrics
        $trajectory = $this->getTrajectory($world, 50);
        $metricsSnapshot = $this->metricsService->calculate($snapshot, $trajectory);
        $metricsArray = $metricsSnapshot->toArray();

        // Compute alerts
        $alertResult = $this->alertEngine->evaluate($metricsSnapshot, $snapshot->year);

        // Compute attractors
        $cosmic = $snapshot->cosmic;
        $attractorCatalog = \App\Domains\Cosmic\ValueObjects\Attractor::catalog();
        $attractors = [];
        foreach ($attractorCatalog as $att) {
            $attractors[] = [
                'code' => $att->code,
                'is_current' => $att->code === $cosmic->currentAttractor,
                'equilibrium' => $att->equilibriumParams,
                'distance_to_current' => $this->calculateDistance($cosmic, $att),
                'transitions' => $att->transitions,
            ];
        }

        // Build trajectory chart data
        $rawSnapshots = $world->cosmicSnapshots()
            ->latest('year')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        $trajectoryLabels = $rawSnapshots->map(fn($s) => $s->year)->toArray();
        $trajectoryEntropy = $rawSnapshots->map(fn($s) => $s->state_data['cosmic']['entropy'] ?? 0)->toArray();
        $trajectoryStability = $rawSnapshots->map(fn($s) => $s->state_data['cosmic']['stability'] ?? 0)->toArray();
        $trajectoryStrain = $rawSnapshots->map(fn($s) => $s->state_data['cosmic']['strain'] ?? 0)->toArray();
        $trajectoryEnergy = $rawSnapshots->map(fn($s) => $s->state_data['cosmic']['energy'] ?? 0)->toArray();

        return view('admin.wmcp.god-console.index', [
            'world' => $world,
            'currentEpoch' => $snapshot->year,
            'metrics' => $metricsArray,
            'alerts' => $alertResult['alerts'] ?? [],
            'composites' => $alertResult['composites'] ?? [],
            'attractors' => $attractors,
            'stateVector' => [
                $cosmic->entropy,
                $cosmic->energy,
                $cosmic->stability,
                $cosmic->strain,
                $cosmic->causalDensity ?? 0,
            ],
            'trajectoryLabels' => $trajectoryLabels,
            'trajectoryEntropy' => $trajectoryEntropy,
            'trajectoryStability' => $trajectoryStability,
            'trajectoryStrain' => $trajectoryStrain,
            'trajectoryEnergy' => $trajectoryEnergy,
            'trajectoryData' => $rawSnapshots->toArray(),
        ]);
    }

    /**
     * GET /admin/wmcp/god-console/{worldId}/metrics
     * Current MetricsSnapshot — all 18 KPIs.
     */
    public function metrics(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $snapshot = $this->getLatestSnapshot($world);

        if (!$snapshot) {
            return response()->json(['error' => 'No simulation data'], 404);
        }

        $trajectory = $this->getTrajectory($world, 50);
        $metrics = $this->metricsService->calculate($snapshot, $trajectory);

        return response()->json([
            'world_id' => $worldId,
            'metrics' => $metrics->toArray(),
        ]);
    }

    /**
     * GET /admin/wmcp/god-console/{worldId}/trajectory
     * Recent trajectory — N snapshots for trend analysis.
     */
    public function trajectory(Request $request, int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $limit = min(200, max(10, (int)$request->input('limit', 50)));

        $snapshots = $world->cosmicSnapshots()
            ->latest('year')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'world_id' => $worldId,
            'count' => $snapshots->count(),
            'trajectory' => $snapshots->map(fn($s) => [
                'year' => $s->year,
                'cosmic' => $s->state_data['cosmic'] ?? [],
                'environment' => $s->state_data['environment'] ?? [],
                'civilization' => $s->state_data['civilization'] ?? [],
            ])->toArray(),
        ]);
    }

    /**
     * GET /admin/wmcp/god-console/{worldId}/attractors
     * All attractor data for the inspector.
     */
    public function attractors(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $snapshot = $this->getLatestSnapshot($world);

        if (!$snapshot) {
            return response()->json(['error' => 'No simulation data'], 404);
        }

        // Return current attractor info and state distance
        $cosmic = $snapshot->cosmic;
        $currentAttractor = $cosmic->currentAttractor;

        $attractorCatalog = \App\Domains\Cosmic\ValueObjects\Attractor::catalog();
        $attractors = [];

        foreach ($attractorCatalog as $att) {
            $attractors[] = [
                'code' => $att->code,
                'is_current' => $att->code === $currentAttractor,
                'equilibrium' => $att->equilibriumParams,
                'distance_to_current' => $this->calculateDistance($cosmic, $att),
                'transitions' => $att->transitions,
            ];
        }

        return response()->json([
            'world_id' => $worldId,
            'current_attractor' => $currentAttractor,
            'attractors' => $attractors,
        ]);
    }

    /**
     * GET /admin/wmcp/god-console/{worldId}/alerts
     * Active alerts based on current metrics.
     */
    public function alerts(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $snapshot = $this->getLatestSnapshot($world);

        if (!$snapshot) {
            return response()->json(['alerts' => [], 'composites' => []]);
        }

        $trajectory = $this->getTrajectory($world, 50);
        $metrics = $this->metricsService->calculate($snapshot, $trajectory);
        $result = $this->alertEngine->evaluate($metrics, $snapshot->year);

        return response()->json([
            'world_id' => $worldId,
            'epoch' => $snapshot->year,
            'alerts' => $result['alerts'],
            'auto_actions' => $result['auto_actions'],
            'composites' => $result['composites'],
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/alerts/{alertId}/acknowledge
     */
    public function acknowledgeAlert(int $worldId, string $alertId): JsonResponse
    {
        // In production: update alert status in DB
        return response()->json([
            'world_id' => $worldId,
            'alert_id' => $alertId,
            'status' => 'ACKNOWLEDGED',
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/control/freeze
     */
    public function freeze(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $entry = $this->epochControl->freeze($world, 'god_console');

        return response()->json([
            'world_id' => $worldId,
            'action' => 'FREEZE',
            'frozen' => true,
            'log_entry' => $entry,
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/control/resume
     */
    public function resume(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $entry = $this->epochControl->resume($world, 'god_console');

        return response()->json([
            'world_id' => $worldId,
            'action' => 'RESUME',
            'frozen' => false,
            'log_entry' => $entry,
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/control/step
     */
    public function step(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $entry = $this->epochControl->stepOne($world);

        return response()->json([
            'world_id' => $worldId,
            'action' => 'STEP_ONE',
            'log_entry' => $entry,
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/control/rollback
     */
    public function rollback(int $worldId): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $restored = $this->epochControl->rollback($world);

        if (!$restored) {
            return response()->json([
                'error' => 'Insufficient history for rollback',
            ], 400);
        }

        return response()->json([
            'world_id' => $worldId,
            'action' => 'ROLLBACK',
            'restored_epoch' => $restored->year,
        ]);
    }

    /**
     * POST /admin/wmcp/god-console/{worldId}/emergency/{action}
     *
     * Supported actions: entropy-shock, reduce-rigidity, force-collapse, toggle-emergent
     */
    public function emergency(Request $request, int $worldId, string $action): JsonResponse
    {
        $world = World::findOrFail($worldId);
        $snapshot = $this->getLatestSnapshot($world);

        if (!$snapshot) {
            return response()->json(['error' => 'No simulation data'], 404);
        }

        $result = match ($action) {
            'entropy-shock' => [
                'snapshot' => $this->emergencyService->injectEntropyShock(
                    $snapshot,
                    (float)$request->input('magnitude', 0.15)
                ),
                'type' => 'ENTROPY_SHOCK',
            ],
            'reduce-rigidity' => [
                'snapshot' => $this->emergencyService->reduceRigidityGlobally(
                    $snapshot,
                    (float)$request->input('reduction', 0.1)
                ),
                'type' => 'REDUCE_RIGIDITY',
            ],
            'force-collapse' => [
                'snapshot' => $this->emergencyService->forceCollapse($snapshot),
                'type' => 'FORCE_COLLAPSE',
            ],
            'toggle-emergent' => [
                'type' => 'TOGGLE_EMERGENT',
                'disabled' => (bool)$request->input('disabled', true),
            ],
            default => null,
        };

        if ($result === null) {
            return response()->json(['error' => "Unknown action: {$action}"], 400);
        }

        if ($action === 'toggle-emergent') {
            $this->emergencyService->setEmergentArchetypesDisabled(
                $result['disabled'],
                $snapshot->year
            );
        }

        return response()->json([
            'world_id' => $worldId,
            'action' => $result['type'],
            'intervention_count' => $this->emergencyService->getInterventionCount(),
            'log' => $this->emergencyService->getInterventionLog(),
        ]);
    }

    // --- Private helpers ---

    private function getLatestSnapshot(World $world): ?WorldSnapshot
    {
        $latest = $world->cosmicSnapshots()->latest('year')->first();

        if (!$latest) {
            return null;
        }

        return WorldSnapshot::fromArray($latest->state_data);
    }

    /**
     * @return WorldSnapshot[]
     */
    private function getTrajectory(World $world, int $limit): array
    {
        $snapshots = $world->cosmicSnapshots()
            ->latest('year')
            ->limit($limit)
            ->get()
            ->reverse();

        return $snapshots->map(function ($s) {
            return WorldSnapshot::fromArray($s->state_data);
        })->toArray();
    }

    private function calculateDistance(\App\Domains\Cosmic\ValueObjects\CosmicState $cosmic, \App\Domains\Cosmic\ValueObjects\Attractor $attractor): float
    {
        $eq = $attractor->equilibriumParams;

        $dEntropy = ($cosmic->entropy - ($eq['entropy'] ?? 0.5)) ** 2;
        $dEnergy = ($cosmic->energy - ($eq['energy'] ?? 0.5)) ** 2;
        $dStability = ($cosmic->stability - ($eq['stability'] ?? 0.5)) ** 2;
        $dStrain = ($cosmic->strain - ($eq['strain'] ?? 0.0)) ** 2;

        return round(sqrt($dEntropy + $dEnergy + $dStability + $dStrain), 4);
    }
}
