<?php

namespace App\Domains\Genre\Genres\Xianxia;

use App\Domains\Genre\Contracts\ProgressionRule;

class CultivationProgression implements ProgressionRule
{
    public function stages(): array
    {
        return [
            'Qi Refining',
            'Foundation Establishment',
            'Core Formation',
            'Nascent Soul',
            'Spirit Severing',
            'Dao Seeking',
            'Immortal Ascension',
        ];
    }

    public function canSkipStage(): bool 
    { 
        return false; 
    }

    public function deathRisk(): float 
    { 
        // Tribulation risk increases with stage, abstract average for now
        return 0.3; 
    }
}
