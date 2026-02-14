<?php

namespace App\Domains\Genre\Signal;

class GenreProfile
{
    /**
     * @param array<string, float> $weights Key: genre_key, Value: 0.0 to 1.0
     */
    public function __construct(
        public readonly array $weights = []
    ) {}

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
        return array_key_first($sorted);
    }
}
