<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Regimes;

/**
 * RegimeParameters — Value Object lưu tham số toán học của 1 Regime.
 *
 * Theo thiết kế RSCD v1.1 (Appendix_01.md):
 * J_k = I + α[(A_k − I) − η_k I]
 *
 * Mỗi Regime có ma trận A_k riêng và hệ số damping η_k riêng,
 * chia sẻ chung α toàn cục (alpha).
 */
final class RegimeParameters
{
    /**
     * @param string $name         Tên Regime (VD: R1_STABLE, R2_INNOVATION...)
     * @param float  $eta          Intrinsic damping (η_k > 0) — mỗi Regime một giá trị khác nhau
     * @param array  $aMatrix      Ma trận A_k (row-stochastic) — flattened 1D array (n*n)
     * @param int    $dimension    Chiều n của state vector
     * @param float  $alpha        Damping step size toàn cục (dùng chung cho mọi Regime)
     * @param float  $lambda       Diffusion coefficient (= 0 nếu single-region)
     * @param float  $beta         Input scale factor (β << 1)
     * @param float  $deltaTarget  Target spectral margin (δ ≥ 0.05)
     * @param float  $gammaCap     Input norm bound (||u|| ≤ γ_cap)
     * @param float  $rMax         State norm bound
     */
    public function __construct(
        public readonly string $name,
        public readonly float  $eta,
        public readonly array  $aMatrix,
        public readonly int    $dimension,
        public readonly float  $alpha       = 0.25,
        public readonly float  $lambda      = 0.0,
        public readonly float  $beta        = 0.01,
        public readonly float  $deltaTarget = 0.08,
        public readonly float  $gammaCap    = 1.5,
        public readonly float  $rMax        = 1000.0,
    ) {}

    /**
     * Trả ra tham số dạng array để gửi qua gRPC request.
     */
    public function toGrpcParams(): array
    {
        return [
            'alpha'              => $this->alpha,
            'lambda'             => $this->lambda,
            'eta'                => $this->eta,
            'beta'               => $this->beta,
            'delta_target'       => $this->deltaTarget,
            'gamma_cap'          => $this->gammaCap,
            'r_max'              => $this->rMax,
            'energy_rate_limit'  => 1.0 - $this->deltaTarget + 0.01,
        ];
    }

    /**
     * Ma trận Identity dưới dạng flattened array (phục vụ Laplacian L khi không có multi-region).
     */
    public function zeroLMatrix(): array
    {
        return array_fill(0, $this->dimension * $this->dimension, 0.0);
    }
}
