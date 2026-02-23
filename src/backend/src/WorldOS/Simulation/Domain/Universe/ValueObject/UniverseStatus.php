<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Universe\ValueObject;

enum UniverseStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case PAUSED = 'paused';
    case COLLAPSED = 'collapsed';
    case ARCHIVED = 'archived';

    public function canStep(): bool
    {
        return $this === self::RUNNING;
    }
}
