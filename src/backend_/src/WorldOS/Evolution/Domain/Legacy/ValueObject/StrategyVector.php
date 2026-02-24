<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

use InvalidArgumentException;

/**
 * StrategyVector - Represents the probability distribution of a civilization's doctrine.
 * The sum of all dimensions must always equal 1.0.
 */
class StrategyVector
{
    public const DIMENSIONS = ['aggressive', 'defensive', 'mercantile', 'technocratic', 'isolationist'];

    /** @var array<string, float> */
    public readonly array $weights;

    public function __construct(array $weights)
    {
        $sum = 0.0;
        $this->weights = [];
        foreach (self::DIMENSIONS as $dim) {
            $val = max(0.0, $weights[$dim] ?? 0.0);
            $this->weights[$dim] = $val;
            $sum += $val;
        }

        if ($sum <= 0) {
            throw new InvalidArgumentException("Strategy weights sum must be greater than 0");
        }

        // Normalize strictly to 1.0
        foreach (self::DIMENSIONS as $dim) {
            $this->weights[$dim] /= $sum;
        }
    }

    public static function defaultStartingStrategy(): self
    {
        return new self([
            'aggressive' => 0.2,
            'defensive' => 0.3,
            'mercantile' => 0.2,
            'technocratic' => 0.1,
            'isolationist' => 0.2,
        ]);
    }
}
