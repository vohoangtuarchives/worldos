<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Arc\ValueObject;

use InvalidArgumentException;

/**
 * Arc — Represents a defined segment of the universe timeline.
 * Immutable Value Object.
 */
final class Arc
{
    private function __construct(
        private readonly ArcType $type,
        private readonly int $startTick,
        private readonly int $endTick,
        private readonly float $tensionDelta
    ) {
    }

    public static function create(ArcType $type, int $startTick, int $endTick, float $tensionDelta): self
    {
        if ($startTick < 0 || $endTick < 0) {
            throw new InvalidArgumentException("Ticks must be positive integers.");
        }
        if ($startTick >= $endTick) {
            throw new InvalidArgumentException("endTick must be strictly greater than startTick.");
        }

        return new self($type, $startTick, $endTick, $tensionDelta);
    }

    public function getType(): ArcType
    {
        return $this->type;
    }

    public function getStartTick(): int
    {
        return $this->startTick;
    }

    public function getEndTick(): int
    {
        return $this->endTick;
    }

    public function getTensionDelta(): float
    {
        return $this->tensionDelta;
    }

    public function getDuration(): int
    {
        return $this->endTick - $this->startTick;
    }

    public function toArray(): array
    {
        return [
            'type'          => $this->type->value,
            'start_tick'    => $this->startTick,
            'end_tick'      => $this->endTick,
            'tension_delta' => $this->tensionDelta,
            'duration'      => $this->getDuration(),
        ];
    }
}
