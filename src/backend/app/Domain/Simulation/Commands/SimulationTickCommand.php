<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Commands;

use App\Domain\Simulation\Regimes\RegimeFactory;
use App\Domain\Simulation\Regimes\RegimeParameters;
use App\Domain\Simulation\Services\SimulationEngineClientInterface;
use App\Infrastructure\Kernel\ExperimentRepository;
use App\Infrastructure\Kernel\HashChainService;
use App\Infrastructure\Kernel\SnapshotRepository;
use App\Domain\Simulation\Events\TickCompleted;
use App\Domain\Simulation\Events\TickRejected;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

/**
 * SimulationTickCommand — Application Command điều phối 1 vòng tick toàn phần.
 *
 * Luồng thực thi (theo WorldOS 1.0.1):
 *   1. Lấy Regime Parameters từ RegimeFactory (PHP side)
 *   2. Gửi gRPC request tới Rust Engine  (tính toán + Governance)
 *   3. Kiểm tra kết quả
 *   4. Lưu Snapshot (HashChain + SnapshotRepository)
 *   5. Bắn Domain Events (TickCompleted / TickRejected)
 *
 * Không được phép thay đổi tham số giữa chừng (Invariant #10: Parameter Immutability).
 */
final class SimulationTickCommand
{
    public function __construct(
        private readonly SimulationEngineClientInterface $engineClient,
        private readonly ExperimentRepository            $experimentRepo,
        private readonly SnapshotRepository              $snapshotRepo,
        private readonly HashChainService                $hashChain,
        private readonly Dispatcher                      $events,
    ) {}

    /**
     * Thực thi 1 tick mô phỏng.
     *
     * @param string $universeId         UUID của Universe đang chạy
     * @param string $experimentId       UUID của Experiment hiện hành
     * @param int    $tick               Số tick hiện tại
     * @param array  $currentState       Vector trạng thái x(t) — 6 chiều
     * @param array  $controlVector      Vector điều khiển u(t)
     * @param string $currentRegimeName  Tên Regime hiện tại (VD: 'R1', 'R2'...)
     * @param string $previousHash       Hash của snapshot trước (genesis = '0')
     * @param array  $cascadeState       Cascade state [Physics, Chemistry, Biology, Cognition, Culture]
     * @param array  $cascadeThresholds  Ngưỡng chuyển tầng Cascade (4 giá trị)
     * @param array  $lawVector          Law vector cho Cascade Engine (17 giá trị)
     * @return array{success: bool, nextState: array, nextHash: string, message: string}
     */
    public function execute(
        string $universeId,
        string $experimentId,
        int    $tick,
        array  $currentState,
        array  $controlVector,
        string $currentRegimeName,
        string $previousHash,
        array  $cascadeState       = [],
        array  $cascadeThresholds  = [],
        array  $lawVector          = [],
    ): array {
        // 1. Lấy định nghĩa Regime (Pure PHP Domain logic)
        $regime = RegimeFactory::fromName($currentRegimeName);

        // 2. Kết hợp params Regime với Cascade params
        $params = $regime->toGrpcParams();
        if (!empty($cascadeState))      { $params['current_cascade']     = $cascadeState; }
        if (!empty($cascadeThresholds)) { $params['cascade_thresholds']  = $cascadeThresholds; }
        if (!empty($lawVector))         { $params['law_vector']          = $lawVector; }

        $startTime = microtime(true);

        // 3. Gọi Rust Engine qua gRPC
        $engineResponse = $this->engineClient->runTick(
            universeId:    $universeId,
            dimension:     $regime->dimension,
            currentState:  $currentState,
            controlVector: $controlVector,
            aMatrix:       $regime->aMatrix,
            lMatrix:       $regime->zeroLMatrix(),
            params:        $params,
        );

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        // 4a. Nếu Rust Engine từ chối tick (Governance vi phạm)
        if ($engineResponse === null) {
            $this->experimentRepo->storeMetrics($experimentId, $tick, 0.0, 0.0, null, 1);
            $this->events->dispatch(new TickRejected($universeId, $experimentId, $tick, $currentRegimeName));
            return [
                'success'   => false,
                'nextState' => $currentState,
                'nextHash'  => $previousHash,
                'message'   => 'Tick rejected by Rust GovernanceGuard — Invariant violation.',
            ];
        }

        $nextState = $engineResponse['state'];
        $nextCascade = $engineResponse['cascade'];

        // 4b. Tick thành công — tính Hash Chain (Invariant #9)
        $nextHash = $this->hashChain->generateNextHash($previousHash, $nextState);

        // 5. Persist snapshot
        $this->snapshotRepo->storeSnapshot(
            $experimentId,
            $tick,
            $nextState,
            $controlVector,
            $regime->toGrpcParams(),
            $nextCascade,
            $nextHash,
            $previousHash
        );

        // 6. Lưu metrics vào experiment record
        $stateNorm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $nextState)));
        $prevNorm  = sqrt(array_sum(array_map(fn ($v) => $v * $v, $currentState)));
        $ratioR    = $prevNorm > 1e-9 ? $stateNorm / $prevNorm : 0.0;

        $this->experimentRepo->storeMetrics($experimentId, $tick, $stateNorm, $ratioR, null, 0);

        // 7. Bắn Domain Event
        $this->events->dispatch(new TickCompleted(
            universeId:   $universeId,
            experimentId: $experimentId,
            tick:         $tick,
            nextState:    $nextState,
            nextHash:     $nextHash,
            regime:       $currentRegimeName,
            elapsedMs:    $elapsedMs,
        ));

        return [
            'success'   => true,
            'nextState' => $nextState,
            'nextHash'  => $nextHash,
            'message'   => '',
        ];
    }
}
