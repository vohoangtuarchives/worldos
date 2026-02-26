<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Actions;

use App\Domain\Simulation\Commands\SimulationTickCommand;
use App\Domain\Simulation\Regimes\RegimeFactory;
use App\Domain\Simulation\Regimes\RegimeResolver;
use App\Infrastructure\Kernel\ExperimentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RunExperimentAction — Khởi tạo và chạy 1 Experiment đầy đủ.
 *
 * Theo WorldOS 1.0.1 §5 (Experiment Protocol):
 *   - Seed + Params phải được khóa trước khi chạy (Parameter Immutability)
 *   - Mỗi Experiment tự ghi Metadata đầy đủ
 *   - Cho phép Resume từ Snapshot Hash Chain
 *
 * Pattern: Action (Application Layer) → SimulationTickCommand (Domain)
 */
final class RunExperimentAction
{
    public function __construct(
        private readonly SimulationTickCommand $tickCommand,
        private readonly RegimeResolver        $regimeResolver,
        private readonly ExperimentRepository  $experimentRepo,
    ) {}

    /**
     * Chạy một chuỗi N tick cho Universe đã có, bắt đầu từ snapshot hiện tại.
     *
     * @param string $universeId
     * @param array  $initialState     Vector x(0) — 6 chiều
     * @param string $initialRegime    Regime ban đầu ('R1', 'R2' ...)
     * @param string $previousHash     Hash của snapshot trước (genesis = '0')
     * @param array  $params           Tham số kernel (alpha, eta, lambda...)
     * @param int    $ticks            Số ticks cần chạy
     * @param int    $startTick        Tick bắt đầu (0 nếu run mới, N nếu resume)
     * @return array Kết quả: {experiment_id, final_state, final_hash, final_regime, ticks_completed, rejected_count}
     */
    public function execute(
        string $universeId,
        array  $initialState,
        string $initialRegime,
        string $previousHash,
        array  $params,
        int    $ticks = 100,
        int    $startTick = 0,
    ): array {
        $experimentId = (string) Str::uuid();

        // Khóa Experiment (Invariant #10: Parameter Immutability)
        $regimeObj = RegimeFactory::fromName($initialRegime);
        $this->experimentRepo->createExperiment($experimentId, array_merge($params, [
            'kernel_version' => '1.0.1',
            'n_dimension'    => $regimeObj->dimension,
            'x0_hash'        => hash('sha256', json_encode($initialState)),
        ]));

        $currentState  = $initialState;
        $currentRegime = $initialRegime;
        $currentHash   = $previousHash;
        $rejectedCount = 0;
        $completedTick = $startTick;

        // Dữ liệu Cascade Engine mặc định (5 tầng vũ trụ)
        $cascadeState      = $params['cascade_state']      ?? [0.5, 0.5, 0.5, 0.5, 0.5];
        $cascadeThresholds = $params['cascade_thresholds'] ?? [0.7, 0.7, 0.7, 0.7];
        $lawVector         = $params['law_vector']         ?? array_fill(0, 17, 0.1);

        // --- Vòng lặp Tick ---
        for ($t = $startTick; $t < $startTick + $ticks; $t++) {
            // Vector điều khiển zero (không có external intervention)
            $controlVector = array_fill(0, $regimeObj->dimension, 0.0);

            $result = $this->tickCommand->execute(
                universeId:        $universeId,
                experimentId:      $experimentId,
                tick:              $t,
                currentState:      $currentState,
                controlVector:     $controlVector,
                currentRegimeName: $currentRegime,
                previousHash:      $currentHash,
                cascadeState:      $cascadeState,
                cascadeThresholds: $cascadeThresholds,
                lawVector:         $lawVector,
            );

            if (!$result['success']) {
                $rejectedCount++;
                // Nếu 3 lần liên tiếp bị reject → dấu hiệu collapse
                if ($rejectedCount >= 3) {
                    break;
                }
                continue;
            }

            $currentState = $result['nextState'];
            $currentHash  = $result['nextHash'];
            $completedTick = $t + 1;

            // Phát hiện chuyển pha Regime sau mỗi tick
            $observable    = array_map(fn ($xi) => 1.0 / (1.0 + exp(-$xi)), $currentState);
            $transition    = $this->regimeResolver->detectTransition($observable, $currentRegime);
            if ($transition['transitioned']) {
                $currentRegime = $transition['to'];
            }
        }

        return [
            'experiment_id'    => $experimentId,
            'final_state'      => $currentState,
            'final_hash'       => $currentHash,
            'final_regime'     => $currentRegime,
            'ticks_completed'  => $completedTick - $startTick,
            'rejected_count'   => $rejectedCount,
        ];
    }
}
