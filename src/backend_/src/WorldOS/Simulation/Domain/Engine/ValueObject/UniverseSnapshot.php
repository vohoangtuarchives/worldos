<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\ValueObject;

/**
 * Full serializable snapshot of a Universe's state at a given tick.
 * Used for persistence and deterministic replay.
 */
final class UniverseSnapshot
{
    public function __construct(
        public readonly string      $universeId,
        public readonly int         $tick,
        public readonly int         $seed,
        public readonly float       $entropy,
        public readonly float       $stabilityIndex,
        public readonly float       $existenceWeight,
        public readonly StateVector $stateVector,
        public readonly \DateTimeImmutable $capturedAt
    ) {
    }

    public static function capture(
        string      $universeId,
        int         $tick,
        int         $seed,
        float       $entropy,
        float       $stabilityIndex,
        float       $existenceWeight,
        StateVector $stateVector
    ): self {
        return new self(
            $universeId,
            $tick,
            $seed,
            $entropy,
            $stabilityIndex,
            $existenceWeight,
            $stateVector,
            new \DateTimeImmutable()
        );
    }

    public function toArray(): array
    {
        return [
            'universe_id'      => $this->universeId,
            'tick'             => $this->tick,
            'seed'             => $this->seed,
            'entropy'          => $this->entropy,
            'stability_index'  => $this->stabilityIndex,
            'existence_weight' => $this->existenceWeight,
            'state_vector'     => $this->stateVector->toArray(),
            'captured_at'      => $this->capturedAt->format(\DATE_ATOM),
        ];
    }
}
