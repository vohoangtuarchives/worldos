<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\GenreSignalDefinition;

class SurvivalSignalDefinition implements GenreSignalDefinition
{
    public function key(): string
    {
        return 'survival';
    }

    public function vocabularySignals(): array
    {
        return [
            'đói khát' => 0.2,
            'kiệt quệ' => 0.15,
            'lạnh lẽo' => 0.1,
            'sinh tồn' => 0.3,
            'tuyệt vọng' => 0.1
        ];
    }

    public function eventSignals(): array
    {
        return [
            'food_shortage' => 0.4,
            'injury_infection' => 0.3,
            'extreme_weather' => 0.2
        ];
    }

    public function consistencyRules(float $dominance): array
    {
        if ($dominance > 0.7) {
            return [
                'forbid' => ['flowerly_descriptions', 'overly_emotional_narrative'],
                'favor'  => ['short_sentences', 'physical_sensations']
            ];
        }
        return [];
    }
}
