<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Fields;

/**
 * ScarMemory — Vết sẹo cấu trúc lịch sử (Structural Memory).
 *
 * Theo AXIOM 10 (WorldOS 1.0.1 §2) và Appendix_03.md §2.2:
 *   S(t+1) = S(t) + H(CollapseSeverity) - η * R(t)         [η << 1]
 *
 * Scar KHÔNG phải Entropy. Scar làm suy giảm Energy Cap:
 *   E_max_eff = E_max - β * ||S||
 *
 * Tính chất bất biến (Immutable):
 *   - Không thể xóa (append-only)
 *   - Phục hồi cực kỳ chậm (η << 1)
 *   - Một phần Scar truyền sang Universe con khi collapse
 *
 * Lan truyền qua Multiverse (Appendix_04.md §3.2):
 *   ΔS_j = ε * Φ_ij * g(S_i)
 */
final class ScarMemory
{
    // Tốc độ phục hồi cực kỳ nhỏ (η << 1)
    private const BASE_HEALING_RATE = 0.002;

    // Tỷ lệ Scar truyền sang Universe con khi collapse (κ ∈ [0,1])
    private const INHERITANCE_RATIO = 0.35;

    // Hệ số suy giảm Energy Cap (β)
    private const ENERGY_CAP_PENALTY = 0.15;

    // Scar tối đa trước khi kích hoạt cascade collapse
    private const CRITICAL_SCAR_LEVEL = 0.85;

    public function __construct(
        public readonly string $universeId,
        private float          $magnitude = 0.0,   // ||S|| ∈ [0, 1]
        private int            $collapseCount = 0,
        private bool           $isCritical = false,
    ) {}

    /**
     * Tiêu hóa một cú sụp đổ — tích lũy Scar theo cường độ sụp đổ.
     *
     * @param float $severity Mức độ nghiêm trọng của collapse ∈ [0, 1]
     *                        Tính từ Tension Integral (Appendix_02 §1.4)
     */
    public function recordCollapse(float $severity): void
    {
        $this->collapseCount++;

        // H(CollapseSeverity) tăng dần theo số lần sụp đổ (lão hóa)
        $accumulation = $severity * (1.0 + 0.1 * $this->collapseCount);
        $this->magnitude = min(1.0, $this->magnitude + $accumulation);

        $this->isCritical = $this->magnitude >= self::CRITICAL_SCAR_LEVEL;
    }

    /**
     * Phục hồi từng tick (cực kỳ chậm).
     * λ_eff = λ_0 + αC + βI − γω  (Appendix_04 §3.1)
     *
     * @param float $cohesion   Observable Cohesion ∈ (0,1)
     * @param float $innovation Observable Innovation ∈ (0,1)
     */
    public function heal(float $cohesion = 0.5, float $innovation = 0.5): void
    {
        $healingRate = self::BASE_HEALING_RATE
            + 0.002 * $cohesion
            + 0.001 * $innovation;

        $this->magnitude = max(0.0, $this->magnitude - $healingRate);
        $this->isCritical = $this->magnitude >= self::CRITICAL_SCAR_LEVEL;
    }

    /**
     * Tính hiệu quả Energy Cap sau khi bị Scar suy giảm.
     *   E_max_eff = E_max * (1 - β * ||S||)
     *
     * @param float $eMax Energy Cap gốc (mặc định = 1000.0)
     * @return float Energy Cap hiệu dụng
     */
    public function effectiveEnergyCapRatio(): float
    {
        return max(0.1, 1.0 - self::ENERGY_CAP_PENALTY * $this->magnitude);
    }

    /**
     * Tính phần Scar truyền sang Universe con khi collapse.
     *   s_child = κ * s_parent   (κ = INHERITANCE_RATIO)
     *
     * @return float Magnitude của Scar kế thừa
     */
    public function computeInheritance(): float
    {
        return $this->magnitude * self::INHERITANCE_RATIO;
    }

    /**
     * Tính lực PTSD vĩ mô — ngăn văn minh tiến về vùng cực đoan gây sụp đổ.
     *   PTSD_force = e^{-γ * ||S||}
     *
     * @return float Thừa số điều chỉnh Exploration (1=tự do, gần 0=bị ức chế mạnh)
     */
    public function computePtsdFactor(float $gamma = 3.0): float
    {
        return exp(-$gamma * $this->magnitude);
    }

    public function getMagnitude(): float     { return $this->magnitude; }
    public function isCritical(): bool        { return $this->isCritical; }
    public function getCollapseCount(): int   { return $this->collapseCount; }

    /**
     * Xuất ra array để persist vào DB.
     */
    public function toArray(): array
    {
        return [
            'universe_id'    => $this->universeId,
            'magnitude'      => $this->magnitude,
            'collapse_count' => $this->collapseCount,
            'is_critical'    => $this->isCritical,
            'energy_cap_ratio' => $this->effectiveEnergyCapRatio(),
        ];
    }
}
