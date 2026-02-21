<?php

declare(strict_types=1);

namespace Tuzy\Domain\Genre\ValueObject;

/**
 * @param array<string, float> $weights Key: genre_key, Value: 0.0 to 1.0
 */
final readonly class GenreProfile
{
    public function __construct(
        public array $weights = [],
    ) {
    }

    public function getWeight(string $genreKey): float
    {
        return $this->weights[$genreKey] ?? 0.0;
    }

    public function dominantGenre(): string
    {
        if (empty($this->weights)) {
            return 'mundane';
        }
        $sorted = $this->weights;
        arsort($sorted);
        $first = array_key_first($sorted);
        return $first ?? 'mundane';
    }
}
