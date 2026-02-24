<?php

declare(strict_types=1);

namespace App\WorldOS\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Cascade State Vector — 5-layer cascade: X = [P, C, B, N, K]^T.
 *
 * Represents the emergence levels across the fundamental layers:
 *   P — Physics index      [0,1]
 *   C — Chemistry index    [0,1]
 *   B — Biology index      [0,1]
 *   N — Cognition index    [0,1]
 *   K — Culture index      [0,1]
 *
 * Each upper layer can only emerge when its lower layer exceeds a threshold.
 * Cascade is strictly ordered: P → C → B → N → K.
 *
 * Immutable Value Object.
 */
final readonly class CascadeStateVector
{
    public function __construct(
        public float $physics,
        public float $chemistry,
        public float $biology,
        public float $cognition,
        public float $culture,
    ) {
        $this->validateAll();
    }

    /**
     * Create with deltas. Clamps to [0, 1].
     *
     * @param array<string, float> $deltas
     */
    public function withDelta(array $deltas): self
    {
        return new self(
            physics: self::clamp($this->physics + ($deltas['physics'] ?? 0.0)),
            chemistry: self::clamp($this->chemistry + ($deltas['chemistry'] ?? 0.0)),
            biology: self::clamp($this->biology + ($deltas['biology'] ?? 0.0)),
            cognition: self::clamp($this->cognition + ($deltas['cognition'] ?? 0.0)),
            culture: self::clamp($this->culture + ($deltas['culture'] ?? 0.0)),
        );
    }

    /**
     * Get the highest active layer index (0-based).
     * Returns -1 if no layer is active (all near zero).
     */
    public function highestActiveLayer(float $threshold = 0.1): int
    {
        $layers = [$this->physics, $this->chemistry, $this->biology, $this->cognition, $this->culture];
        $highest = -1;

        foreach ($layers as $index => $value) {
            if ($value >= $threshold) {
                $highest = $index;
            }
        }

        return $highest;
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'physics' => $this->physics,
            'chemistry' => $this->chemistry,
            'biology' => $this->biology,
            'cognition' => $this->cognition,
            'culture' => $this->culture,
        ];
    }

    /**
     * @param array<string, float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            physics: (float) ($data['physics'] ?? 0.0),
            chemistry: (float) ($data['chemistry'] ?? 0.0),
            biology: (float) ($data['biology'] ?? 0.0),
            cognition: (float) ($data['cognition'] ?? 0.0),
            culture: (float) ($data['culture'] ?? 0.0),
        );
    }

    /**
     * Initial state: only physics exists.
     */
    public static function initial(): self
    {
        return new self(
            physics: 1.0,
            chemistry: 0.0,
            biology: 0.0,
            cognition: 0.0,
            culture: 0.0,
        );
    }

    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }

    private static function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    private function validateAll(): void
    {
        foreach ($this->toArray() as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new InvalidArgumentException(
                    "Cascade dimension '{$name}' must be in [0.0, 1.0], got: {$value}"
                );
            }
        }
    }
}
