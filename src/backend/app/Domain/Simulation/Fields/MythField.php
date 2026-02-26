<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Fields;

/**
 * MythField — Trường Huyền Thoại (Slow-decay Field).
 *
 * Theo AXIOM 9 (WorldOS 1.0.1 §2) và Appendix_03.md §2.1:
 *   m(t+1) = α * m(t) + F(MajorEvents)       [0.95 < α < 1]
 *
 * Myth bẻ cong quỹ đạo tiến hóa thông qua Bias Vector:
 *   x(t+1) = J * x(t) + G(m(t))
 *
 * Myth KHÔNG override Jacobian J (tuân thủ AXIOM 7).
 * Nó chỉ shift Exploration Force của văn minh.
 *
 * Lifecycle: Belief lặp lại → Emergence → Active → Decay/Merge → Scar
 */
final class MythField
{
    private const DECAY_RATE = 0.97; // α ∈ (0.95, 1) — phân rã rất chậm

    // Cường độ tối đa của Myth trước khi bão hòa
    private const MAX_STRENGTH = 1.0;

    // Ngưỡng để Myth được coi là "Active" (đủ mạnh để tác động)
    private const ACTIVE_THRESHOLD = 0.3;

    // Ngưỡng để Myth "chết" (quá yếu, trở thành Scar mờ nhạt)
    private const DECAY_THRESHOLD = 0.05;

    public function __construct(
        public readonly string $universeId,
        public readonly string $mythId,
        public readonly string $name,
        private float          $strength = 0.0,
        private int            $tickCreated = 0,
        private string         $status = 'dormant',   // dormant | active | decaying | dead
    ) {}

    /**
     * Cập nhật Myth Field theo phương trình phân rã chậm.
     * Gọi mỗi epoch (không phải mỗi tick).
     *
     * @param float $eventBoost  Lực từ Major Events bồi đắp cho Myth F(events)
     */
    public function evolve(float $eventBoost = 0.0): void
    {
        // m(t+1) = α * m(t) + F(events)
        $this->strength = min(
            self::MAX_STRENGTH,
            self::DECAY_RATE * $this->strength + $eventBoost
        );

        // Cập nhật trạng thái
        $this->status = match(true) {
            $this->strength <= self::DECAY_THRESHOLD  => 'dead',
            $this->strength <= self::ACTIVE_THRESHOLD => 'decaying',
            default                                   => 'active',
        };
    }

    /**
     * Tính Bias Vector G(m) — ảnh hưởng lên Control Input u(t).
     * Myth điều hướng Exploration Force, không override luật vật lý.
     *
     * Mỗi chiều của state vector bị dịch chuyển nhỏ theo cường độ Myth.
     * Biên độ bị giới hạn để không vi phạm γ_cap (AXIOM 7).
     *
     * @param int $dimension Chiều n của state vector
     * @param float $biasMagnitude Cường độ tối đa của bias (mặc định rất nhỏ)
     * @return array Vector bias có n phần tử
     */
    public function computeBiasVector(int $dimension, float $biasMagnitude = 0.05): array
    {
        if (!$this->isActive()) {
            return array_fill(0, $dimension, 0.0);
        }

        // Bias tỷ lệ với cường độ Myth hiện tại
        $scale = $this->strength * $biasMagnitude;
        return array_fill(0, $dimension, $scale);
    }

    /**
     * Kiểm tra Myth có đang đủ mạnh để tác động không.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->strength > self::ACTIVE_THRESHOLD;
    }

    /**
     * Kiểm tra Myth đã "chết" (chờ chuyển thành Scar hoặc xóa).
     */
    public function isDead(): bool
    {
        return $this->status === 'dead';
    }

    public function getStrength(): float { return $this->strength; }
    public function getStatus(): string  { return $this->status; }

    /**
     * Xuất ra array để persist vào DB.
     */
    public function toArray(): array
    {
        return [
            'myth_id'     => $this->mythId,
            'universe_id' => $this->universeId,
            'name'        => $this->name,
            'strength'    => $this->strength,
            'status'      => $this->status,
            'tick_created'=> $this->tickCreated,
        ];
    }
}
