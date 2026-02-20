<?php

namespace App\Domains\WorldEvolution\Events;

use Carbon\Carbon;

abstract class WorldEvent
{
    public readonly string $eventId;
    public readonly Carbon $occurredAt;
    
    public function __construct(
        public readonly string $sagaId,
        public readonly string $universeId,
        public readonly int $year,
        public readonly float $severity = 1.0
    ) {
        $this->eventId = uniqid('wevt_');
        $this->occurredAt = Carbon::now();
    }

    /**
     * Tên mô tả sự kiện (e.g., "Sụp đổ Ma pháp", "Đại chiến Hỗn mang").
     */
    abstract public function getName(): string;

    /**
     * Vector tác động lên WorldState khi sự kiện này xảy ra.
     * Trả về mảng thay đổi các chiều (ví dụ: ['entropy' => +0.2, 'order' => -0.1])
     */
    abstract public function getImpactVector(): array;
}
