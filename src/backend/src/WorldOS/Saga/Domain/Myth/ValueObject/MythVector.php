<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\ValueObject;

use InvalidArgumentException;

/**
 * MythVector — 5D latent coordinate system for a Saga's narrative theme.
 *
 * Defines the macro direction of the story:
 * 1. Ascension: Growing beyond limits, rising
 * 2. Corruption: Decay, falling to dark impulses
 * 3. Recursion: Repeating cycles, systemic loops
 * 4. Escape: Fleeing from the system/universe logic
 * 5. Convergence: Harmonizing with the universe
 */
final class MythVector
{
    public const DIM_ASCENSION   = 'ascension';
    public const DIM_CORRUPTION  = 'corruption';
    public const DIM_RECURSION  = 'recursion';
    public const DIM_ESCAPE      = 'escape';
    public const DIM_CONVERGENCE = 'convergence';

    public const DIMENSIONS = [
        self::DIM_ASCENSION,
        self::DIM_CORRUPTION,
        self::DIM_RECURSION,
        self::DIM_ESCAPE,
        self::DIM_CONVERGENCE,
    ];

    /**
     * @param array<string, float> $components Range [0.0, 1.0]
     */
    private function __construct(private readonly array $components)
    {
    }

    public static function create(array $components): self
    {
        $clamped = [];
        foreach (self::DIMENSIONS as $dim) {
            $value = $components[$dim] ?? 0.0;
            $clamped[$dim] = max(0.0, min(1.0, $value));
        }

        $clamped = self::enforceOpposition($clamped);

        return new self($clamped);
    }

    private static function enforceOpposition(array $v): array
    {
        $pairs = [
            [self::DIM_ASCENSION, self::DIM_CORRUPTION],
            [self::DIM_ESCAPE, self::DIM_CONVERGENCE],
        ];

        foreach ($pairs as [$a, $b]) {
            $sum = $v[$a] + $v[$b];
            if ($sum > 1.0) {
                $v[$a] /= $sum;
                $v[$b] /= $sum;
            }
        }

        return $v;
    }

    public static function genesis(): self
    {
        // A neutral myth vector starts mostly blank, ready to be shaped
        return self::create([
            self::DIM_ASCENSION   => 0.1,
            self::DIM_CORRUPTION  => 0.1,
            self::DIM_RECURSION   => 0.1,
            self::DIM_ESCAPE      => 0.1,
            self::DIM_CONVERGENCE => 0.1,
        ]);
    }

    public function get(string $dimension): float
    {
        if (!in_array($dimension, self::DIMENSIONS, true)) {
            throw new InvalidArgumentException("Unknown MythVector dimension: {$dimension}");
        }
        return $this->components[$dimension];
    }

    public function toArray(): array
    {
        return $this->components;
    }

    /**
     * Returns the dominant dimension (the one with the highest value).
     */
    public function getDominantDimension(): string
    {
        $maxKey = self::DIM_ASCENSION;
        $maxVal = -1.0;

        foreach ($this->components as $dim => $value) {
            if ($value > $maxVal) {
                $maxVal = $value;
                $maxKey = $dim;
            }
        }

        return $maxKey;
    }
}
