<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Entities;

/**
 * Genre as vector of scores (0..1) per genre; primary = argmax.
 */
final class GenreVector
{
    /** @var array<string, float> */
    private array $scores;

    /**
     * @param array<string, float> $scores
     */
    public function __construct(array $scores)
    {
        $this->scores = $scores;
    }

    /**
     * @return array<string, float>
     */
    public function getScores(): array
    {
        return $this->scores;
    }

    public function getPrimary(): string
    {
        if (empty($this->scores)) {
            return 'neutral';
        }
        $copy = $this->scores;
        arsort($copy, SORT_NUMERIC);
        return array_key_first($copy);
    }

    public function get(string $genre): float
    {
        return $this->scores[$genre] ?? 0.0;
    }
}
