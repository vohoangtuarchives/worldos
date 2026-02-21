<?php

namespace Tuzy\Application\Genre\Signal;

use Tuzy\Domain\Genre\Contracts\VocabularyMap;
use Tuzy\Domain\Genre\ValueObject\GenreProfile;

class VocabularyResolver
{
    public function __construct(
        private array $dictionaries // Key: genre_key, Value: VocabularyMap
    ) {}

    public function resolve(GenreProfile $profile): array
    {
        $merged = [];
        $weights = $profile->weights;

        foreach ($this->dictionaries as $key => $dict) {
            $weight = $weights[$key] ?? 0.0;
            if ($weight <= 0) continue;

            $words = $dict->getWords(); // Assuming getWords() returns the map
            foreach ($words as $term => $meaning) {
                // If weight is high, this word dominates
                if (!isset($merged[$term]) || $weight > ($weights[$merged[$term]['source']] ?? 0)) {
                    $merged[$term] = [
                        'meaning' => $meaning,
                        'source' => $key,
                        'weight' => $weight
                    ];
                }
            }
        }

        return $merged;
    }
}
