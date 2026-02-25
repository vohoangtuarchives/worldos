<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * World State Vector — 6D core state of a Universe.
 *
 * Represents the macro-state of a civilization at a given moment.
 * Immutable Value Object — every mutation produces a new instance.
 *
 * Dimensions:
 *   entropy    [0,1]: Trật tự → Heat Death
 *   order      [0,1]: Vô chính phủ → Toàn trị
 *   innovation [0,1]: Đồ đá → Singularity
 *   cohesion   [0,1]: Nội chiến → Hive Mind
 *   inequality [0,1]: Utopia → Oligarchy
 *   trauma     [0,1]: Thời bình → Post-Apocalyptic
 */
final readonly class WorldStateVector
{
    public function __construct(
        public float $entropy,
        public float $order,
        public float $innovation,
        public float $cohesion,
        public float $inequality,
        public float $trauma,
    ) {
        $this->validateAll();
    }

    /**
     * Create a new vector with deltas applied. Clamps result to [0, 1].
     *
     * @param array<string, float> $deltas e.g. ['entropy' => +0.05, 'order' => -0.02]
     */
    public function withDelta(array $deltas): self
    {
        return new self(
            entropy: self::clamp($this->entropy + ($deltas['entropy'] ?? 0.0)),
            order: self::clamp($this->order + ($deltas['order'] ?? 0.0)),
            innovation: self::clamp($this->innovation + ($deltas['innovation'] ?? 0.0)),
            cohesion: self::clamp($this->cohesion + ($deltas['cohesion'] ?? 0.0)),
            inequality: self::clamp($this->inequality + ($deltas['inequality'] ?? 0.0)),
            trauma: self::clamp($this->trauma + ($deltas['trauma'] ?? 0.0)),
        );
    }

    /**
     * Euclidean distance between two state vectors.
     */
    public function distanceTo(self $other): float
    {
        return sqrt(
            ($this->entropy - $other->entropy) ** 2
            + ($this->order - $other->order) ** 2
            + ($this->innovation - $other->innovation) ** 2
            + ($this->cohesion - $other->cohesion) ** 2
            + ($this->inequality - $other->inequality) ** 2
            + ($this->trauma - $other->trauma) ** 2
        );
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'entropy' => $this->entropy,
            'order' => $this->order,
            'innovation' => $this->innovation,
            'cohesion' => $this->cohesion,
            'inequality' => $this->inequality,
            'trauma' => $this->trauma,
        ];
    }

    /**
     * @param array<string, float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entropy: (float) ($data['entropy'] ?? 0.0),
            order: (float) ($data['order'] ?? 0.0),
            innovation: (float) ($data['innovation'] ?? 0.0),
            cohesion: (float) ($data['cohesion'] ?? 0.0),
            inequality: (float) ($data['inequality'] ?? 0.0),
            trauma: (float) ($data['trauma'] ?? 0.0),
        );
    }

    /**
     * Zero state — pristine universe.
     */
    public static function zero(): self
    {
        return new self(0.0, 0.0, 0.0, 0.0, 0.0, 0.0);
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
                    "State dimension '{$name}' must be in [0.0, 1.0], got: {$value}"
                );
            }
        }
    }
}
