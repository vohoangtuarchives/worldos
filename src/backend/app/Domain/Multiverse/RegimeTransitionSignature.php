<?php

declare(strict_types=1);

namespace App\Domain\Multiverse;

/**
 * RegimeTransitionSignature (RTS) — Di sản cấu trúc của Universe sau collapse.
 *
 * Theo Appendix_01.md §2.1:
 * Thay vì lưu chuỗi regime dài, ta trích xuất 4 đặc trưng cốt lõi
 * làm "DNA" cho Universe con.
 */
final class RegimeTransitionSignature
{
    /**
     * @param array  $transitionMatrix      Ma trận xác suất Markov P_ij (5×5 = 25 phần tử)
     * @param array  $dwellVector           Tỷ lệ thời gian tại mỗi Regime [R1%,R2%,R3%,R4%,R5%]
     * @param int    $oscillationIndex      Số lần flip-flop giữa 2 regime đối nghịch (R3↔R4)
     * @param string $collapsePrecursorHash Hash mã hóa 10 tick ngay trước collapse
     * @param string $collapseType          A=Overexpansion, B=Polarization, C=Entropy, D=Critical Edge
     * @param float  $regimeEntropy         H = -Σ D_i log D_i (đa dạng hóa regime)
     */
    public function __construct(
        public readonly array  $transitionMatrix,
        public readonly array  $dwellVector,
        public readonly int    $oscillationIndex,
        public readonly string $collapsePrecursorHash,
        public readonly string $collapseType,
        public readonly float  $regimeEntropy,
    ) {}

    /**
     * Phân loại kiểu sụp đổ theo RSCD v1.1 Appendix_01 §3.3.
     */
    public static function classifyCollapseType(array $dwellVector, int $oscillationIndex): string
    {
        [$d1, $d2, $d3, $d4, $d5] = $dwellVector;

        // Type A: Innovation Overexpansion (R2 dominant, R3 thấp)
        if ($d2 > 0.35 && $oscillationIndex < 5) {
            return 'A'; // Overexpansion
        }

        // Type B: Polarization Spiral (OI rất cao, R3↔R4 ping-pong)
        if ($oscillationIndex > 10) {
            return 'B'; // Polarization Spiral
        }

        // Type D: Critical Edge (ở biên stability quá lâu)
        if ($d4 > 0.25) {
            return 'D'; // Critical Edge
        }

        // Type C: Entropy Drift (mòn mỏi từ từ)
        return 'C';
    }

    /**
     * Tính Regime Entropy để đo tính đa dạng của lịch sử.
     * H = -Σ D_i log D_i (Shannon Entropy)
     * Entropy cao = lịch sử đa dạng → giữ lại để nhân bản.
     * Entropy thấp = lịch sử đơn điệu → ưu tiên đột biến mạnh.
     */
    public static function computeEntropy(array $dwellVector): float
    {
        $h = 0.0;
        foreach ($dwellVector as $d) {
            if ($d > 1e-9) {
                $h -= $d * log($d);
            }
        }
        return $h;
    }

    public function toArray(): array
    {
        return [
            'transition_matrix'       => $this->transitionMatrix,
            'dwell_vector'            => $this->dwellVector,
            'oscillation_index'       => $this->oscillationIndex,
            'collapse_precursor_hash' => $this->collapsePrecursorHash,
            'collapse_type'           => $this->collapseType,
            'regime_entropy'          => $this->regimeEntropy,
        ];
    }
}
