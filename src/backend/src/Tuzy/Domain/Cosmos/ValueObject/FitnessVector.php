<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmos\ValueObject;

/**
 * Vật thể giá trị đa chiều đại diện cho "chất lượng" của một vũ trụ.
 * Thay thế điểm số scalar (đơn lẻ) để hỗ trợ chọn lọc Pareto.
 */
final class FitnessVector
{
    public function __construct(
        public readonly float $stability,      // Độ ổn định (tránh sụp đổ quá nhanh)
        public readonly float $complexity,     // Độ phức tạp/kịch tính của sự kiện
        public readonly float $diversity,      // Sự đa dạng về ý thức hệ/văn hóa
        public readonly float $selfReference,  // Khả năng tự tham chiếu (Hero emergence)
        public readonly float $coherence       // Tính nhất quán của định luật
    ) {}

    /**
     * Kiểm tra xem vector này có bị lấn át bởi vector khác không (Pareto Dominance).
     */
    public function isDominatedBy(FitnessVector $other): bool
    {
        $atLeastOneBetter = false;

        // Vector A bị lấn át bởi B nếu B không tệ hơn ở bất kỳ chiều nào 
        // và tốt hơn ở ít nhất một chiều.
        $dimensions = [
            $other->stability >= $this->stability,
            $other->complexity >= $this->complexity,
            $other->diversity >= $this->diversity,
            $other->selfReference >= $this->selfReference,
            $other->coherence >= $this->coherence
        ];

        foreach ($dimensions as $isNotWorse) {
            if (!$isNotWorse) return false;
        }

        $atLeastOneBetter = (
            $other->stability > $this->stability ||
            $other->complexity > $this->complexity ||
            $other->diversity > $this->diversity ||
            $other->selfReference > $this->selfReference ||
            $other->coherence > $this->coherence
        );

        return $atLeastOneBetter;
    }

    public function toArray(): array
    {
        return [
            'stability' => $this->stability,
            'complexity' => $this->complexity,
            'diversity' => $this->diversity,
            'selfReference' => $this->selfReference,
            'coherence' => $this->coherence,
        ];
    }
}
