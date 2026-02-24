<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\ValueObject;

enum Severity: string
{
    case LOW      = 'low';
    case MEDIUM   = 'medium';
    case HIGH     = 'high';
    case CRITICAL = 'critical';

    public static function fromIntensity(float $intensity): self
    {
        return match(true) {
            $intensity >= 1.0 => self::CRITICAL,
            $intensity >= 0.7 => self::HIGH,
            $intensity >= 0.4 => self::MEDIUM,
            default           => self::LOW,
        };
    }

    public function rank(): int
    {
        return match($this) {
            self::LOW      => 1,
            self::MEDIUM   => 2,
            self::HIGH     => 3,
            self::CRITICAL => 4,
        };
    }
}
