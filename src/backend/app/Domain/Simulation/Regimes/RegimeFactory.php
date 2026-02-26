<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Regimes;

/**
 * RegimeFactory — Tạo ra 5 Regime tiêu chuẩn theo RSCD v1.1 (Appendix_01.md).
 *
 * Core Dimensions (theo thứ tự cố định):
 *   [0] Entropy (E)
 *   [1] Order (O)
 *   [2] Innovation (I)
 *   [3] Cohesion (C)
 *   [4] Inequality (Q)
 *   [5] Trauma (T)
 *
 * Ma trận A_k là 6×6 row-stochastic, flattened thành 36 phần tử.
 * Chuỗi emergent: R1→R2→R3→R4→R5→R1 (luân hồi tự nhiên)
 */
final class RegimeFactory
{
    private const N = 6; // Core Dimensions = 6

    /**
     * R1 — Stable Civilization 🟢
     * Triết lý: Order giảm Entropy, Cohesion giảm Trauma, Innovation tăng nhẹ Order.
     * η = 0.30 (damping cao, diagonal dominance mạnh)
     */
    public static function stableCivilization(): RegimeParameters
    {
        // Cột: E    O    I    C    Q    T
        $a = [
            //E:
            0.70, 0.10, 0.05, 0.05, 0.05, 0.05,
            //O:
            0.05, 0.75, 0.10, 0.05, 0.03, 0.02,
            //I:
            0.05, 0.10, 0.70, 0.05, 0.05, 0.05,
            //C:
            0.05, 0.05, 0.05, 0.75, 0.05, 0.05,
            //Q:
            0.10, 0.05, 0.05, 0.05, 0.70, 0.05,
            //T:
            0.05, 0.05, 0.05, 0.10, 0.05, 0.70,
        ];

        return new RegimeParameters(
            name:      'R1_STABLE_CIVILIZATION',
            eta:       0.30,
            aMatrix:   $a,
            dimension: self::N,
        );
    }

    /**
     * R2 — Innovation Surge 🟡
     * Triết lý: Innovation đẩy mạnh Order & Inequality, Cohesion yếu dần.
     * η = 0.22 (gần biên critical, coupling Innovation mạnh)
     */
    public static function innovationSurge(): RegimeParameters
    {
        $a = [
            //E:
            0.60, 0.10, 0.10, 0.05, 0.10, 0.05,
            //O:
            0.05, 0.65, 0.20, 0.05, 0.03, 0.02,
            //I:
            0.05, 0.15, 0.60, 0.05, 0.10, 0.05,
            //C:
            0.10, 0.05, 0.10, 0.60, 0.10, 0.05,
            //Q:
            0.10, 0.05, 0.15, 0.05, 0.60, 0.05,
            //T:
            0.05, 0.05, 0.05, 0.05, 0.10, 0.70,
        ];

        return new RegimeParameters(
            name:      'R2_INNOVATION_SURGE',
            eta:       0.22,
            aMatrix:   $a,
            dimension: self::N,
        );
    }

    /**
     * R3 — Polarization 🟠
     * Triết lý: Vòng lặp phản hồi Inequality↔Trauma, Cohesion giảm, Entropy tăng theo Trauma.
     * η = 0.18 (sát spectral margin, oscillation xã hội)
     */
    public static function polarization(): RegimeParameters
    {
        $a = [
            //E:
            0.55, 0.05, 0.05, 0.05, 0.15, 0.15,
            //O:
            0.10, 0.55, 0.10, 0.10, 0.10, 0.05,
            //I:
            0.05, 0.05, 0.60, 0.05, 0.15, 0.10,
            //C:
            0.10, 0.10, 0.05, 0.55, 0.10, 0.10,
            //Q:
            0.10, 0.05, 0.05, 0.05, 0.55, 0.20,
            //T:
            0.15, 0.05, 0.05, 0.05, 0.20, 0.50,
        ];

        return new RegimeParameters(
            name:      'R3_POLARIZATION',
            eta:       0.18,
            aMatrix:   $a,
            dimension: self::N,
        );
    }

    /**
     * R4 — Turbulence 🔴
     * Triết lý: Trauma lan sang mọi chiều, Entropy tăng cực mạnh, Order suy yếu.
     * η = 0.12 (damping rất thấp, ρ(J) ≈ 0.90–0.92)
     */
    public static function turbulence(): RegimeParameters
    {
        $a = [
            //E:
            0.50, 0.07, 0.08, 0.05, 0.15, 0.15,
            //O:
            0.15, 0.50, 0.10, 0.10, 0.10, 0.05,
            //I:
            0.07, 0.08, 0.55, 0.05, 0.15, 0.10,
            //C:
            0.10, 0.10, 0.05, 0.50, 0.10, 0.15,
            //Q:
            0.12, 0.07, 0.06, 0.05, 0.50, 0.20,
            //T:
            0.15, 0.05, 0.05, 0.05, 0.20, 0.50,
        ];

        return new RegimeParameters(
            name:      'R4_TURBULENCE',
            eta:       0.12,
            aMatrix:   $a,
            dimension: self::N,
        );
    }

    /**
     * R5 — Collapse Basin ⚫
     * Triết lý: Strong damping, convergence về low-energy attractor → "reset mềm".
     * η = 0.45 (diagonal dominance cực kỳ mạnh, coupling gần như triệt tiêu)
     */
    public static function collapseBasin(): RegimeParameters
    {
        $a = [
            //E:
            0.90, 0.02, 0.02, 0.02, 0.02, 0.02,
            //O:
            0.02, 0.90, 0.02, 0.02, 0.02, 0.02,
            //I:
            0.02, 0.02, 0.90, 0.02, 0.02, 0.02,
            //C:
            0.02, 0.02, 0.02, 0.90, 0.02, 0.02,
            //Q:
            0.02, 0.02, 0.02, 0.02, 0.90, 0.02,
            //T:
            0.02, 0.02, 0.02, 0.02, 0.02, 0.90,
        ];

        return new RegimeParameters(
            name:      'R5_COLLAPSE_BASIN',
            eta:       0.45,
            aMatrix:   $a,
            dimension: self::N,
        );
    }

    /**
     * Trả về Regime theo tên string (dùng cho dispatcher hoặc config).
     */
    public static function fromName(string $name): RegimeParameters
    {
        return match (strtoupper($name)) {
            'R1', 'R1_STABLE_CIVILIZATION'  => self::stableCivilization(),
            'R2', 'R2_INNOVATION_SURGE'      => self::innovationSurge(),
            'R3', 'R3_POLARIZATION'          => self::polarization(),
            'R4', 'R4_TURBULENCE'            => self::turbulence(),
            'R5', 'R5_COLLAPSE_BASIN'        => self::collapseBasin(),
            default => throw new \InvalidArgumentException("Unknown Regime: {$name}"),
        };
    }
}
