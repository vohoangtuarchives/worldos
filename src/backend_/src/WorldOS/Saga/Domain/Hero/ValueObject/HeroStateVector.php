<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

use InvalidArgumentException;

/**
 * HeroStateVector (8D) — The micro-dynamical state vector of the hero.
 * Replaces the procedural properties (stress, conviction, traumaStack).
 * Values are floats constrained to [0.0, 1.0].
 */
final class HeroStateVector
{
    public const DIM_STRESS      = 'stress';
    public const DIM_CONVICTION  = 'conviction';
    public const DIM_RESILIENCE  = 'resilience';
    public const DIM_ADAPTATION  = 'adaptation';
    public const DIM_FEAR        = 'fear';
    public const DIM_CLARITY     = 'clarity';
    public const DIM_EGO         = 'ego';
    public const DIM_TRAUMA      = 'trauma';

    public const DIMENSIONS = [
        self::DIM_STRESS,
        self::DIM_CONVICTION,
        self::DIM_RESILIENCE,
        self::DIM_ADAPTATION,
        self::DIM_FEAR,
        self::DIM_CLARITY,
        self::DIM_EGO,
        self::DIM_TRAUMA,
    ];

    /**
     * @param array<string, float> $components
     */
    private function __construct(private readonly array $components)
    {
    }

    public static function genesis(HeroProfile $profile): self
    {
        $components = [
            self::DIM_STRESS      => 0.0,
            self::DIM_CONVICTION  => $profile->getSeedConviction(),
            self::DIM_RESILIENCE  => 0.5, // Baseline
            self::DIM_ADAPTATION  => 0.2,
            self::DIM_FEAR        => 0.0,
            self::DIM_CLARITY     => 0.8,
            self::DIM_EGO         => 0.3,
            self::DIM_TRAUMA      => 0.0,
        ];

        return new self($components);
    }

    public static function fromArray(array $data): self
    {
        $components = [];
        foreach (self::DIMENSIONS as $dim) {
            $components[$dim] = max(0.0, min(1.0, $data[$dim] ?? 0.0));
        }
        return new self($components);
    }

    public static function createRaw(array $components): self
    {
        // Does not clamp, used for intermediate math (like projection results)
        return new self($components);
    }

    public function get(string $dimension): float
    {
        if (!in_array($dimension, self::DIMENSIONS, true)) {
            throw new InvalidArgumentException("Unknown hero dimension: {$dimension}");
        }
        return $this->components[$dimension] ?? 0.0;
    }

    public function toArray(): array
    {
        return $this->components;
    }

    public function toIndexedArray(): array
    {
        return [
            $this->get(self::DIM_STRESS),
            $this->get(self::DIM_CONVICTION),
            $this->get(self::DIM_RESILIENCE),
            $this->get(self::DIM_ADAPTATION),
            $this->get(self::DIM_FEAR),
            $this->get(self::DIM_CLARITY),
            $this->get(self::DIM_EGO),
            $this->get(self::DIM_TRAUMA),
        ];
    }

    public static function fromIndexedArray(array $indexed): self
    {
        $components = [
            self::DIM_STRESS      => $indexed[0] ?? 0.0,
            self::DIM_CONVICTION  => $indexed[1] ?? 0.0,
            self::DIM_RESILIENCE  => $indexed[2] ?? 0.0,
            self::DIM_ADAPTATION  => $indexed[3] ?? 0.0,
            self::DIM_FEAR        => $indexed[4] ?? 0.0,
            self::DIM_CLARITY     => $indexed[5] ?? 0.0,
            self::DIM_EGO         => $indexed[6] ?? 0.0,
            self::DIM_TRAUMA      => $indexed[7] ?? 0.0,
        ];
        return new self($components);
    }

    public function add(self $other): self
    {
        $newComponents = [];
        foreach (self::DIMENSIONS as $dim) {
            $newComponents[$dim] = $this->get($dim) + $other->get($dim);
        }
        return new self($newComponents);
    }

    public function clamp(float $min = 0.0, float $max = 1.0): self
    {
        $newComponents = [];
        foreach (self::DIMENSIONS as $dim) {
            $newComponents[$dim] = max($min, min($max, $this->get($dim)));
        }
        return new self($newComponents);
    }

    /**
     * Logistic bounding (sigmoid mapping) over unbounded value. 
     * Keeps vectors smoothly inside [0, 1] without hard clamping.
     */
    public function logisticBound(): self
    {
        $newComponents = [];
        foreach (self::DIMENSIONS as $dim) {
            $v = $this->get($dim);
            // Sigmoid: 1 / (1 + e^-v) but we want to map unbounded positive/negative 
            // values nicely. If the input is already mostly 0-1, standard sigmoid shifts it.
            // A simpler piecewise soft-clip or specific scaling could be used. 
            // For now, let's use a soft logistic curve around 0.5:
            // f(x) = 1 / (1 + exp(-4 * (x - 0.5)))
            $newComponents[$dim] = 1.0 / (1.0 + exp(-4.0 * ($v - 0.5)));
        }
        return new self($newComponents);
    }
}
