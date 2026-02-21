<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\PhaseSignal;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;

/**
 * PhaseEngine
 * 
 * Determines the logical "Phase" of a civilization based on MCS (Macro Civilization State) metrics.
 * Each phase influences event probability and narrative tone.
 */
class PhaseEngine
{
    public const PHASE_GOLDEN_AGE = 'golden_age';
    public const PHASE_STAGNATION = 'stagnation';
    public const PHASE_FRAGMENTATION = 'fragmentation';
    public const PHASE_WAR = 'war';
    public const PHASE_REFORM = 'reform';
    public const PHASE_STABILITY = 'stability';

    /**
     * Determine current phase based on civilization state.
     */
    public function determinePhase(CivilizationState $civ): string
    {
        $p = $civ->prosperity;
        $s = $civ->stability;
        $e = $civ->internalEntropy;
        $m = $civ->militaryPressure;
        $t = $civ->technologicalLevel;

        // Priority-based phase resolution
        if ($m > 0.7) {
            return self::PHASE_WAR;
        }

        if ($e > 0.6 && $s < 0.4) {
            return self::PHASE_FRAGMENTATION;
        }

        if ($p > 0.8 && $s > 0.7) {
            return self::PHASE_GOLDEN_AGE;
        }

        if ($p > 0.6 && $e > 0.5) {
            return self::PHASE_STAGNATION;
        }

        if ($s > 0.6 && $t > 1.0) {
            return self::PHASE_REFORM;
        }

        return self::PHASE_STABILITY;
    }

    public function getLexicon(string $phase): array
    {
        return match($phase) {
            self::PHASE_GOLDEN_AGE => [
                'imagery' => ['trăm hoa đua nở', 'thịnh vượng', 'huy hoàng', 'thanh bình', 'hào quang'],
                'symbols' => ['phượng hoàng', 'lúa vàng', 'đình đài', 'ánh dương', 'ngọc ngà'],
                'verbs' => ['bừng sáng', 'vươn mình', 'kết tinh', 'lan tỏa', 'thang hoa'],
                'tone' => 'vibrant_epic',
            ],
            self::PHASE_WAR => [
                'imagery' => ['khói lửa', 'loạn lạc', 'bi thương', 'kiên cường', 'ngút trời'],
                'symbols' => ['gươm đao', 'ngựa chiến', 'cờ xí', 'máu chiến', 'tiếng trống'],
                'verbs' => ['rung chuyển', 'tan tác', 'trỗi dậy', 'quyết tử', 'xung kích'],
                'tone' => 'martial_dark',
            ],
            self::PHASE_FRAGMENTATION => [
                'imagery' => ['rạn nứt', 'chia cắt', 'u ám', 'hoài nghi', 'ly tán'],
                'symbols' => ['mảnh vỡ', 'bụi mờ', 'hoang phế', 'đại ngàn', 'vực thẳm'],
                'verbs' => ['tan vỡ', 'phân hóa', 'lung lay', 'khép lại', 'sụp đổ'],
                'tone' => 'melancholic_chaos',
            ],
            self::PHASE_REFORM => [
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
}
