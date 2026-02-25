<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel\Stability;

final class StabilityBudgetMonitor
{
    /**
     * Xác thực Năng lượng (Energy / Trạng thái giới hạn rời rạc) có bùng nổ hàm mũ không.
     * $prevNorm tính từ vector ẩn $x(t-1)$
     * $nextNorm tính từ vector ẩn $x(t)$
     */
    public function check(array $xPrev, array $xNext): float
    {
        $prevNorm = $this->norm($xPrev);
        $nextNorm = $this->norm($xNext);

        if ($prevNorm === 0.0) {
            return 0.0;
        }

        return $nextNorm / $prevNorm;
    }

    private function norm(array $v): float
    {
        $sumSq = 0.0;
        foreach ($v as $val) {
            $sumSq += $val * $val;
        }
        return sqrt($sumSq);
    }
}
