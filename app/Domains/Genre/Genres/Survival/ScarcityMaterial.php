<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\MaterialSystem;

class ScarcityMaterial implements MaterialSystem
{
    public function primaryMaterial(): string
    {
        return 'caloric_energy'; // The fundamental currency is life energy/food
    }

    public function subtypes(): array
    {
        return [
            'water' => 'Clean Water',
            'food' => 'Edible Ration',
            'stamina' => 'Physical Stamina',
            'sanity' => 'Mental Resilience',
            'warmth' => 'Body Heat'
        ];
    }

    public function conversionRules(): array
    {
        return [
            'rest' => ['stamina' => 10, 'food' => -5, 'water' => -5],
            'travel' => ['stamina' => -20, 'food' => -2, 'water' => -5],
            'cold' => ['warmth' => -10, 'food' => -5],
            'scavenge' => ['stamina' => -15, 'sanity' => -5],
        ];
    }
}
