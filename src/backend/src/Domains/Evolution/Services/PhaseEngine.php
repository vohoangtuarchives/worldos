<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\Enums\CivilizationPhase;
use WorldOS\Domains\Evolution\ValueObjects\PhaseSignal;
use WorldOS\Domains\Evolution\ValueObjects\CosmicState;
use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;

/**
 * PhaseEngine
 * 
 * Determines the logical "Phase" of a civilization based on MCS (Macro Civilization State) metrics.
 * Now supports DYNAMICS: Gradients, Curvature and Phase Inertia.
 */
class PhaseEngine
{
    /**
     * Determine current phase based on civilization state and its dynamics.
     */
    public function determinePhase(
        CivilizationSnapshot $current,
        ?CivilizationSnapshot $prev = null,
        float $curvature = 0.0
    ): CivilizationPhase {
        $p = $current->prosperity;
        $s = $current->stability;
        $e = $current->internalEntropy;
        $m = $current->militaryPressure;
        $t = $current->technologicalLevel;

        // Calculate Gradients (Dynamism)
        $dP = $prev ? ($p - $prev->prosperity) : 0.0;
        $dS = $prev ? ($s - $prev->stability) : 0.0;

        // 1. PHASE SHOCK (Priority: CURVATURE)
        // If the reality is warping too fast (High Curvature), history breaks.
        if ($curvature > 0.08) {
            return ($dS < 0 || $e > 0.5) ? CivilizationPhase::FRAGMENTATION : CivilizationPhase::REFORM;
        }

        // 2. WAR (High Military Pressure)
        if ($m > 0.7 || ($m > 0.5 && $prev && $m > $prev->militaryPressure + 0.1)) {
            return CivilizationPhase::WAR;
        }

        // 3. FRAGMENTATION (Entropy or Stability collapse)
        if ($e > 0.6 || $s < 0.2 || ($dS < -0.15 && $s < 0.4)) {
            return CivilizationPhase::FRAGMENTATION;
        }

        // 4. GOLDEN AGE (High Prosperity AND Growth)
        // Dynamic check: Must be prosperous AND either stable or growing fast.
        if ($p > 0.7 && $s > 0.6 && ($dP > 0 || $p > 0.85)) {
            return CivilizationPhase::GOLDEN_AGE;
        }

        // 5. STAGNATION (High Prosperity but Decaying)
        if ($p > 0.6 && ($dP < -0.02 || $e > 0.5)) {
            return CivilizationPhase::STAGNATION;
        }

        // 6. REFORM (Stability is recovering or High Tech potential)
        if ($dS > 0.05 || ($s > 0.6 && $t > 1.2)) {
            return CivilizationPhase::REFORM;
        }

        return CivilizationPhase::STABILITY;
    }

    public function getLexicon(CivilizationPhase $phase): array
    {
        return match($phase) {
            CivilizationPhase::GOLDEN_AGE => [
                'imagery' => ['trăm hoa đua nở', 'thịnh vượng', 'huy hoàng', 'thanh bình', 'hào quang'],
                'symbols' => ['phượng hoàng', 'lúa vàng', 'đình đài', 'ánh dương', 'ngọc ngà'],
                'verbs' => ['bừng sáng', 'vươn mình', 'kết tinh', 'lan tỏa', 'thăng hoa'],
                'tone' => 'vibrant_epic',
            ],
            CivilizationPhase::WAR => [
                'imagery' => ['khói lửa', 'loạn lạc', 'bi thương', 'kiên cường', 'ngút trời'],
                'symbols' => ['gươm đao', 'ngựa chiến', 'cờ xí', 'máu chiến', 'tiếng trống'],
                'verbs' => ['rung chuyển', 'tan tác', 'trỗi dậy', 'quyết tử', 'xung kích'],
                'tone' => 'martial_dark',
            ],
            CivilizationPhase::FRAGMENTATION => [
                'imagery' => ['rạn nứt', 'chia cắt', 'u ám', 'hoài nghi', 'ly tán'],
                'symbols' => ['mảnh vỡ', 'bụi mờ', 'hoang phế', 'đại ngàn', 'vực thẳm'],
                'verbs' => ['tan vỡ', 'phân hóa', 'lung lay', 'khép lại', 'sụp đổ'],
                'tone' => 'metancholic_chaos',
            ],
            CivilizationPhase::REFORM => [
                'imagery' => ['đổi mới', 'ánh bình minh', 'kỳ vọng', 'chuyển mình'],
                'symbols' => ['mầm non', 'trang sách', 'ngọn đuốc', 'bánh xe'],
                'verbs' => ['cải cách', 'trà kiến', 'khởi sắc', 'thanh trừng'],
                'tone' => 'optimistic_stern',
            ],
            default => [
                'imagery' => ['trầm mặc', 'ổn định', 'bình lặng', 'kiên cố'],
                'symbols' => ['dòng sông', 'làng quê', 'mây trắng', 'lũy tre'],
                'verbs' => ['duy trì', 'tiếp nối', 'lắng đọng', 'giữ vững'],
                'tone' => 'neutral_steady',
            ]
        };
    }

    /**
     * Get physical forces (drifts) associated with a phase.
     * These are applied directly to the Field Equations.
     */
    public function getPhaseForces(CivilizationPhase $phase): array
    {
        return match($phase) {
            CivilizationPhase::GOLDEN_AGE => [
                'prosperity' => 0.03,
                'stability' => 0.02,
                'ie' => -0.01,
                'ce' => 0.02,
            ],
            CivilizationPhase::WAR => [
                'mp' => 0.08,
                'prosperity' => -0.04,
                'ie' => 0.05,
                'tech' => 0.02, // War breeds tech
                'stability' => -0.03,
            ],
            CivilizationPhase::FRAGMENTATION => [
                'stability' => -0.1,
                'sc' => -0.08,
                'ie' => 0.08,
                'legitimacy' => -0.05,
            ],
            CivilizationPhase::REFORM => [
                'stability' => 0.15, // Cải cách mạnh mẽ
                'legitimacy' => 0.08,
                'ie' => -0.1, // Giảm entropy hữu hiệu
                'tech' => 0.05,
            ],
            CivilizationPhase::STAGNATION => [
                'ce' => -0.02,
                'prosperity' => -0.01,
                'ie' => 0.02,
                'tech' => -0.01,
            ],
            default => [
                'stability' => 0.005, // Slight bias towards stability in normal times
            ]
        };
    }
}
