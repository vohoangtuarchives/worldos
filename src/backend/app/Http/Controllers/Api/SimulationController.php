<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Simulation\Actions\RunExperimentAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * SimulationController — API Gateway cho Experiment Orchestration.
 *
 * Theo triết lý CQRS + Physics-first:
 *   - POST /api/simulation/experiments        → Khởi tạo + chạy Experiment
 *   - GET  /api/simulation/universes/{id}/state → Đọc Observable State từ Snapshot
 *
 * Không có vòng lặp tính toán trong Controller — mọi tính toán
 * đều qua Domain Command → Rust Engine.
 */
final class SimulationController extends Controller
{
    public function __construct(
        private readonly RunExperimentAction $runExperimentAction,
    ) {}

    /**
     * POST /api/simulation/experiments
     * Chạy N ticks của 1 Universe — trả về final state và metadata Experiment.
     */
    public function runExperiment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'universe_id'   => 'required|uuid',
            'initial_regime'=> 'required|string|in:R1,R2,R3,R4,R5',
            'initial_state' => 'nullable|array|min:6|max:6',
            'previous_hash' => 'nullable|string',
            'ticks'         => 'nullable|integer|min:1|max:10000',
            'params'        => 'nullable|array',
        ]);

        try {
            $initialState = $validated['initial_state'] ?? [0.0, 0.0, 0.0, 0.0, 0.0, 0.0];
            $previousHash = $validated['previous_hash'] ?? '0';
            $ticks        = $validated['ticks'] ?? 100;
            $params       = $validated['params'] ?? [];

            $result = $this->runExperimentAction->execute(
                universeId:    $validated['universe_id'],
                initialState:  $initialState,
                initialRegime: $validated['initial_regime'],
                previousHash:  $previousHash,
                params:        $params,
                ticks:         $ticks,
            );

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/simulation/universes/{universeId}/snapshot
     * Đọc Snapshot cuối cùng của Universe (Observable State + Hash).
     */
    public function getSnapshot(Request $request, string $universeId): JsonResponse
    {
        // Snapshot đọc từ Redis hoặc DB (append-only, không thay đổi vòng lặp)
        $snapshot = \Illuminate\Support\Facades\DB::table('kernel_experiment_snapshots')
            ->where('experiment_id', function ($query) use ($universeId) {
                $query->select('id')
                    ->from('kernel_experiments')
                    ->whereRaw("JSON_EXTRACT(config, '$.universe_id') = ?", [$universeId])
                    ->orderByDesc('started_at')
                    ->limit(1);
            })
            ->orderByDesc('tick')
            ->first();

        if (!$snapshot) {
            return response()->json(['success' => false, 'message' => 'No snapshot found'], 404);
        }

        $stateVector = json_decode($snapshot->state_vector, true);
        $observable  = array_map(fn ($xi) => 1.0 / (1.0 + exp(-$xi)), $stateVector);

        return response()->json([
            'success' => true,
            'data'    => [
                'tick'             => $snapshot->tick,
                'snapshot_hash'    => $snapshot->snapshot_hash,
                'observable_state' => $observable,
                'dimension_labels' => ['Entropy', 'Order', 'Innovation', 'Cohesion', 'Inequality', 'Trauma'],
            ],
        ]);
    }
}
