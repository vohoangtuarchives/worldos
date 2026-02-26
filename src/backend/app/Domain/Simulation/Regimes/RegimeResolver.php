<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Regimes;

/**
 * RegimeResolver — Phát hiện Regime hiện hành và chuyển pha từ Observable State.
 *
 * Theo RSCD v1.1 (Appendix_01.md §1.3):
 *   Chuỗi Emergent: R1 → R2 → R3 → R4 → R5 → R1
 *
 * Observable State S(t) = sigmoid(x(t)) ∈ (0,1):
 *   [0] Entropy (E), [1] Order (O), [2] Innovation (I)
 *   [3] Cohesion (C), [4] Inequality (Q), [5] Trauma (T)
 *
 * Dẫu Rust GovernanceGuard kiểm soát ổn định toán học của hệ thống,
 * RegimeResolver kiểm soát NGÔN NGỮ (semantic) — Regime nào đang thống trị.
 */
final class RegimeResolver
{
    // --- Ngưỡng chuyển pha (Phase Transition Thresholds) ---

    // R1 → R2 (Stable → Innovation Surge)
    private const INNOVATION_SURGE_THRESHOLD = 0.65;

    // R2 → R3 (Innovation → Polarization)
    private const POLARIZATION_INEQUALITY_THRESHOLD = 0.70;
    private const POLARIZATION_COHESION_THRESHOLD   = 0.40;

    // R3 → R4 (Polarization → Turbulence)
    private const TURBULENCE_TRAUMA_THRESHOLD  = 0.65;
    private const TURBULENCE_ENTROPY_THRESHOLD = 0.75;

    // R4 → R5 (Turbulence → Collapse Basin)
    private const COLLAPSE_ORDER_THRESHOLD  = 0.20;
    private const COLLAPSE_COHESION_MINIMUM = 0.15;

    /**
     * Phát hiện Regime phù hợp nhất dựa trên Observable State hiện tại.
     *
     * @param array $observable  S(t) = sigmoid(x(t))  — 6 giá trị ∈ (0,1)
     * @param string $currentRegime  Regime đang hoạt động (dùng để áp dụng hysteresis)
     * @return string Tên Regime mới (R1, R2, R3, R4, R5)
     */
    public function resolve(array $observable, string $currentRegime): string
    {
        [$entropy, $order, $innovation, $cohesion, $inequality, $trauma] = $observable;

        // --- R5: Collapse Basin (ưu tiên phát hiện sớm nhất) ---
        if ($order < self::COLLAPSE_ORDER_THRESHOLD && $cohesion < self::COLLAPSE_COHESION_MINIMUM) {
            return 'R5';
        }

        // --- R4: Turbulence ---
        if ($trauma > self::TURBULENCE_TRAUMA_THRESHOLD && $entropy > self::TURBULENCE_ENTROPY_THRESHOLD) {
            return 'R4';
        }

        // --- R3: Polarization ---
        if ($inequality > self::POLARIZATION_INEQUALITY_THRESHOLD && $cohesion < self::POLARIZATION_COHESION_THRESHOLD) {
            return 'R3';
        }

        // --- R2: Innovation Surge ---
        if ($innovation > self::INNOVATION_SURGE_THRESHOLD) {
            return 'R2';
        }

        // --- R1: Stable Civilization (default attractor) ---
        return 'R1';
    }

    /**
     * Tính Stability Margin xấp xỉ từ Observable State.
     * Đây là ước tính macro (không dùng ma trận Jacobian đầy đủ).
     * Margin thực sự được tính bởi Rust Engine.
     *
     * @param string $regime Regime hiện tại
     * @return float Stability Margin xấp xỉ (1 - η_k)
     */
    public function estimateStabilityMargin(string $regime): float
    {
        // Dựa vào η_k từ RegimeFactory — margin ≈ α * η_k
        return match (strtoupper($regime)) {
            'R1' => 0.92,  // η=0.30 → rất ổn định
            'R2' => 0.85,  // η=0.22 → gần biên
            'R3' => 0.78,  // η=0.18 → sát spectral limit
            'R4' => 0.72,  // η=0.12 → hỗn loạn có kiểm soát
            'R5' => 0.95,  // η=0.45 → reset mềm
            default => 0.80,
        };
    }

    /**
     * Phát hiện chuyển pha và trả về thông tin transition.
     *
     * @return array{transitioned: bool, from: string, to: string, cause: string}
     */
    public function detectTransition(array $observable, string $currentRegime): array
    {
        $newRegime = $this->resolve($observable, $currentRegime);
        $transitioned = $newRegime !== $currentRegime;

        $cause = '';
        if ($transitioned) {
            [$entropy, $order, $innovation, $cohesion, $inequality, $trauma] = $observable;
            $key = "{$currentRegime}→{$newRegime}";
            $cause = match($key) {
                'R1→R2' => "Innovation Surge: innovation=" . round($innovation, 3),
                'R2→R3' => "Polarization: inequality=" . round($inequality, 3) . ", cohesion=" . round($cohesion, 3),
                'R3→R4' => "Turbulence: trauma=" . round($trauma, 3) . ", entropy=" . round($entropy, 3),
                'R4→R5' => "Collapse Basin: order=" . round($order, 3) . ", cohesion=" . round($cohesion, 3),
                'R5→R1' => "Reconstruction: system reset to Stable",
                default  => "Regime shift: {$currentRegime} → {$newRegime}",
            };
        }

        return [
            'transitioned' => $transitioned,
            'from'         => $currentRegime,
            'to'           => $newRegime,
            'cause'        => $cause,
        ];
    }
}
