<?php

namespace App\Domains\Genre\Definitions;

class UrbanFantasySignal
{
    public function getSignalProfile(): array
    {
        return [
            'modern_setting' => [
                'keywords' => ['camera', 'internet', 'police', 'skyscraper', 'car'],
                'weight' => 0.4
            ],
            'hidden_world' => [
                'keywords' => ['sect', 'ancient', 'seal', 'martial_arts', 'bloodline'],
                'weight' => 0.3
            ],
            'masquerade_breach' => [
                'keywords' => ['leak', 'video', 'witness', 'panic', 'cover_up'],
                'weight' => 0.3,
                'indicates_transition' => true 
            ]
        ];
    }
}
