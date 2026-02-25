<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel\Stability;

use App\Modules\Simulation\Matrix\MatrixOperator;

final class GershgorinResult
{
    /**
     * @param float $maxBound
     * @param int[] $violations
     */
    public function __construct(
        public readonly float $maxBound,
        public readonly array $violations
    ) {}
}

final class GershgorinAnalyzer
{
    /**
     * Đánh giá bảo chứng stability condition bằng Gershgorin Bound (O(n^2)).
     * Trả về danh sách row bị vi phạm, nếu không có vi phạm, hệ thống được đảm bảo tính Contractive.
     */
    public function verify(MatrixOperator $J): GershgorinResult
    {
        $maxRadius = 0.0;
        $violations = [];
        $d = $J->dimension();

        for ($i = 0; $i < $d; $i++) {
            $row = $J->getRow($i);
            $center = abs($row[$i]);
            $radius = 0.0;

            foreach ($row as $j => $value) {
                if ($j !== $i) {
                    $radius += abs($value);
                }
            }

            $bound = $center + $radius;

            if ($bound >= 1.0) {
                $violations[] = $i;
            }

            $maxRadius = max($maxRadius, $bound);
        }

        return new GershgorinResult(
            $maxRadius,
            $violations
        );
    }
}
