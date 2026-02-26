<?php

declare(strict_types=1);

namespace App\Domain\Multiverse;

use App\Domain\Simulation\Fields\ScarMemory;

/**
 * BranchManager — Xử lý phân nhánh (Branching) và đột biến tham số có định hướng.
 *
 * Theo Appendix_01.md §2.3 và §3:
 *   Đột biến: Δθ = W · Φ(RTS)   [Directed Mutation]
 *   Không dùng Gaussian ngẫu nhiên — dựa vào hình học quá khứ.
 *
 * Sau đột biến: phép chiếu đảm bảo ρ(J) < 1 - δ (ổn định dứt khoát).
 *
 * Cây World Tree khi nhìn vĩ mô là Markov Chain với Stationary Distribution π.
 */
final class BranchManager
{
    // Hệ số đột biến tối đa (% thay đổi so với tham số gốc)
    private const MAX_MUTATION_RATE = 0.15;

    // Hệ số đột biến tối thiểu (đảm bảo đa dạng, ngay cả khi entropy cao)
    private const MIN_MUTATION_RATE = 0.02;

    /**
     * Tạo tham số đột biến hướng dẫn bởi RTS (Regime-Driven Directed Mutation).
     *
     * @param array $parentParams  Tham số Universe cha {alpha, lambda, eta, gamma_cap, delta_target}
     * @param RegimeTransitionSignature $rts  Di sản cấu trúc của Universe cha
     * @param float $scarInheritance  Magnitude Scar kế thừa từ Universe cha
     * @return array Tham số đột biến cho Universe con
     */
    public function computeMutatedParams(
        array  $parentParams,
        RegimeTransitionSignature $rts,
        float  $scarInheritance = 0.0,
    ): array {
        // Cường độ đột biến tự thích nghi: ||Δθ|| = k * (1 - H)
        // Universe càng nhàm chán (entropy thấp), đột biến càng mạnh
        $mutationStrength = max(
            self::MIN_MUTATION_RATE,
            self::MAX_MUTATION_RATE * (1.0 - $rts->regimeEntropy)
        );

        $childParams = $parentParams;

        // --- Chiến lược đột biến theo kiểu sụp đổ ---
        switch ($rts->collapseType) {
            case 'A': // Overexpansion: tăng η đáng kể, giảm coupling để ổn định
                $childParams['eta']       = $this->clampEta($parentParams['eta'] + $mutationStrength * 0.30);
                $childParams['lambda']    = max(0.0, $parentParams['lambda'] - $mutationStrength * 0.05);
                break;

            case 'B': // Polarization Spiral: tăng diffusion λ để phá vòng oscillation
                $childParams['lambda']    = min(0.5, $parentParams['lambda'] + $mutationStrength * 0.12);
                $childParams['eta']       = $this->clampEta($parentParams['eta'] + $mutationStrength * 0.05);
                break;

            case 'D': // Critical Edge: giảm α để tăng stability margin
                $childParams['alpha']     = max(0.1, $parentParams['alpha'] - $mutationStrength * 0.05);
                $childParams['delta_target'] = min(0.15, $parentParams['delta_target'] + $mutationStrength * 0.02);
                break;

            case 'C': // Entropy Drift: tăng nhẹ beta để có thêm input energy
            default:
                $childParams['beta']      = min(0.05, $parentParams['beta'] + $mutationStrength * 0.005);
                break;
        }

        // Áp dụng Scar penalty vào gamma_cap (Scar càng nặng, hệ càng hạn chế input)
        if ($scarInheritance > 0.1) {
            $scarPenalty = 1.0 - 0.1 * $scarInheritance;
            $childParams['gamma_cap'] = max(0.5, $parentParams['gamma_cap'] * $scarPenalty);
        }

        return $childParams;
    }

    /**
     * Kiểm tra xem Universe con có đủ "khác biệt" với Universe cha không.
     * Đảm bảo ε-separated metric tree (Appendix_01 §4.2).
     *
     * @param float $epsilon Khoảng cách tối thiểu (mặc định 0.02)
     */
    public function isSufficientlyDiverse(array $parentParams, array $childParams, float $epsilon = 0.02): bool
    {
        $distance = 0.0;
        $keys = ['alpha', 'lambda', 'eta', 'beta', 'delta_target', 'gamma_cap'];

        foreach ($keys as $key) {
            $distance += abs(($childParams[$key] ?? 0) - ($parentParams[$key] ?? 0));
        }

        return $distance >= $epsilon;
    }

    /**
     * Tính Regime Entropy Score để quyết định prune hay nhân bản.
     * Prune nếu entropy < ngưỡng (universe ngắn ngủi, không thú vị).
     */
    public function shouldPrune(float $regimeEntropy, float $pruneThreshold = 0.3): bool
    {
        return $regimeEntropy < $pruneThreshold;
    }

    /**
     * Clamp η vào vùng an toàn [0.05, 0.50].
     */
    private function clampEta(float $eta): float
    {
        return max(0.05, min(0.50, $eta));
    }
}
