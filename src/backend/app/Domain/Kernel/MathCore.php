<?php

declare(strict_types=1);

namespace App\Domain\Kernel;

use App\Domain\Kernel\Exception\KernelMathException;

/**
 * MathCore — Lớp toán học thuần túy (Pure Deterministic Function).
 *
 * Thực hiện phương trình cập nhật contraction map:
 * x(t+1) = x(t) + α[(A - I)x(t) - λLx(t) - ηx(t) + βu(t)]
 *
 * Viết gọn: x(t+1) = Jx(t) + αβu(t)
 *
 * Không có I/O, không Eloquent, không container injection.
 * Hoàn toàn deterministic, pure function.
 */
final class MathCore
{
    /**
     * Thực hiện một bước cập nhật kernel.
     *
     * @param array<int, float> $x       Latent state vector x(t) ∈ ℝⁿ
     * @param array<int, float> $u       Exogenous input vector u(t), đã bị chặn ‖u‖ ≤ γ_cap
     * @param array<int, array<int, float>> $A Row-stochastic matrix (n×n)
     * @param array<int, array<int, float>> $L Graph Laplacian (n×n), empty if no multi-region
     * @param float $alpha  Damping step size       (0 < α < 1)
     * @param float $lambda Diffusion coefficient   (λ ≥ 0)
     * @param float $eta    Intrinsic damping        (η > 0, MANDATORY)
     * @param float $beta   Input scaling factor     (β ≪ 1)
     * @return array<int, float> x(t+1) — Latent state mới
     */
    public function step(
        array $x,
        array $u,
        array $A,
        array $L,
        float $alpha,
        float $lambda,
        float $eta,
        float $beta
    ): array {
        $n = count($x);

        if (count($u) !== $n) {
            throw KernelMathException::dimensionMismatch($n, count($u));
        }

        $hasL = !empty($L);
        $xNew = [];

        for ($i = 0; $i < $n; $i++) {
            // Compute F_i(x, u) = Σ_j [(A_ij - δ_ij) * x_j] - λ * Σ_j [L_ij * x_j] - η * x_i + β * u_i
            $avgTerm = 0.0;    // (A - I)x contribution
            $diffTerm = 0.0;   // -λLx contribution

            for ($j = 0; $j < $n; $j++) {
                $aVal = $A[$i][$j] ?? 0.0;

                // (A - I) contribution
                if ($i === $j) {
                    $avgTerm += ($aVal - 1.0) * $x[$j];
                } else {
                    $avgTerm += $aVal * $x[$j];
                }

                // -λL contribution
                if ($hasL) {
                    $lVal = $L[$i][$j] ?? 0.0;
                    $diffTerm -= $lambda * $lVal * $x[$j];
                }
            }

            // Intrinsic damping: -ηx_i
            $dampTerm = -$eta * $x[$i];

            // External input: βu_i
            $inputTerm = $beta * $u[$i];

            // F_i = avgTerm + diffTerm + dampTerm + inputTerm
            $fi = $avgTerm + $diffTerm + $dampTerm + $inputTerm;

            // x_i(t+1) = x_i(t) + α * F_i
            $xNew[$i] = $x[$i] + $alpha * $fi;
        }

        return $xNew;
    }

    /**
     * Chuyển đổi latent state x sang observable state S = σ(x).
     * σ(x_i) = 1 / (1 + e^{-x_i}) ∈ (0, 1)
     *
     * @param array<int, float> $x Latent state vector
     * @return array<int, float> Observable state vector
     */
    public function toObservable(array $x): array
    {
        $s = [];
        foreach ($x as $xi) {
            $s[] = 1.0 / (1.0 + exp(-$xi));
        }
        return $s;
    }

    /**
     * Tính L2 norm của vector.
     *
     * @param array<int, float> $v
     * @return float
     */
    public function norm(array $v): float
    {
        $sumSq = 0.0;
        foreach ($v as $val) {
            $sumSq += $val * $val;
        }
        return sqrt($sumSq);
    }

    /**
     * Tính hash SHA256 của state vector cho Snapshot Hash Chain.
     * hash_t = SHA256(hash_{t-1} + serialize(x_t))
     *
     * @param string $previousHash Hash của snapshot tick trước (genesis = '0')
     * @param array<int, float> $x Latent state hiện tại
     * @return string SHA256 hash
     */
    public function snapshotHash(string $previousHash, array $x): string
    {
        $serialized = json_encode($x, JSON_PRESERVE_ZERO_FRACTION);
        return hash('sha256', $previousHash . $serialized);
    }
}
