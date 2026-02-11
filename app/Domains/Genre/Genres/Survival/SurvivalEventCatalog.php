<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\EventCatalog;

class SurvivalEventCatalog implements EventCatalog
{
    public function allowedEvents(): array
    {
        return [
            'scavenge',
            'hunt',
            'rest',
            'hide',
            'flee',
            'craft',
            'consume',
            'injury',
            'sickness',
            'weather_event'
        ];
    }

    public function eventRules(string $eventType): array
    {
        return match ($eventType) {
            'scavenge' => ['risk' => 'high', 'reward' => 'random'],
            'rest' => ['risk' => 'medium', 'reward' => 'stamina_recovery'],
            'hunt' => ['risk' => 'very_high', 'reward' => 'high_food'],
            default => []
        ];
    }
}
