<?php

namespace App\Domains\CivilizationDynamics\ValueObjects;

/**
 * Ký ức văn minh (Residual Memory).
 * Lưu trữ "sẹo" và "sang chấn" của các sự kiện quá khứ (war_trauma, metaphysical_scar).
 * Sẽ phân rã (decay) theo thời gian.
 */
class CivilizationResidual
{
    /** @var array<string, float> */
    private array $scars = [];

    // Tốc độ phân rã tự nhiên / năm
    private const BASE_DECAY_RATE = 0.005;

    public function __construct(array $scars = [])
    {
        $this->scars = $scars;
    }

    public function getScars(): array
    {
        return $this->scars;
    }

    public function getIntensity(string $type): float
    {
        return $this->scars[$type] ?? 0.0;
    }

    public function addScars(array $newScars): void
    {
        foreach ($newScars as $type => $magnitude) {
            $current = $this->scars[$type] ?? 0.0;
            // Cộng dồn logarithm hoặc hard-cap để tránh nổ số, ví dụ max 1.0
            $this->scars[$type] = min(1.0, $current + $magnitude);
        }
    }

    public function decay(int $years): void
    {
        foreach ($this->scars as $type => $magnitude) {
            $decayAmount = $magnitude * self::BASE_DECAY_RATE * $years;
            $newMag = $magnitude - $decayAmount;
            if ($newMag < 0.01) {
                unset($this->scars[$type]); // Lãng quên hoàn toàn
            } else {
                $this->scars[$type] = $newMag;
            }
        }
    }

    public function toArray(): array
    {
        return $this->scars;
    }
}
