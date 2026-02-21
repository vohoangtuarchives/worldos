<?php

namespace Tuzy\Application\Genre\Genres\Xianxia;

use Tuzy\Domain\Genre\Contracts\EventCatalog;

class XianxiaEventCatalog implements EventCatalog
{
    public function allowedEvents(): array
    {
        return [
            'meditation',
            'breakthrough',
            'combat',
            'sect_war',
            'auction',
            'secret_realm_opening',
            'tribulation',
            'pill_refining',
        ];
    }

    public function eventRules(string $eventType): array
    {
        return match($eventType) {
            'tribulation' => ['mortality_rate' => 0.5, 'requires_peak_stage' => true],
            'breakthrough' => ['requires_qi_density' => 0.8],
            default => [],
        };
    }
}
