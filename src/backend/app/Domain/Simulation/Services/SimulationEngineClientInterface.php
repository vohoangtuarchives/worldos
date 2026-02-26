<?php

namespace App\Domain\Simulation\Services;

interface SimulationEngineClientInterface
{
    /**
     * Gửi yêu cầu chạy 1 tick tính toán xuống Rust Engine qua gRPC
     *
     * @param array $currentState Vector trạng thái x(t)
     * @param array $controlVector Vector điều khiển u(t)
     * @param array $aMatrix Ma trận A (Flattened)
     * @param array $lMatrix Ma trận L (Flattened)
     * @param array $params Các tham số hệ thống (alpha, lambda, eta, beta, v.v...)
     * @return array|null Trả về Vector trạng thái x(t+1) nếu thành công, null nếu thất bại/vi phạm Invariants
     */
    /**
     * @return array|null Trả về mảng chứa ['state' => float[], 'cascade' => float[]] hoặc null nếu lỗi
     */
    public function runTick(
        string $universeId,
        int $dimension,
        array $currentState,
        array $controlVector,
        array $aMatrix,
        array $lMatrix,
        array $params
    ): ?array;
}
