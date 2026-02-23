<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\ValueObject;

enum EventType: string
{
    case ANOMALY_SPIKE    = 'anomaly_spike';
    case FORK_TRIGGERED   = 'fork_triggered';
    case ERA_SHIFT        = 'era_shift';
    case TRANSCENDENCE    = 'transcendence';
    case COLLAPSE         = 'collapse';
    case LIFECYCLE_TRANSITION = 'lifecycle_transition';
    case IDEOLOGY_SHIFT    = 'ideology_shift';
    case COSMIC_REBIRTH    = 'cosmic_rebirth';

    public function label(): string
    {
        return match($this) {
            self::ANOMALY_SPIKE  => 'Anomaly Spike',
            self::FORK_TRIGGERED => 'Timeline Fork Triggered',
            self::ERA_SHIFT      => 'Era Shift',
            self::TRANSCENDENCE  => 'Transcendence',
            self::COLLAPSE       => 'Collapse',
            self::LIFECYCLE_TRANSITION => 'Lifecycle Transition',
            self::IDEOLOGY_SHIFT      => 'Ideological Shift',
            self::COSMIC_REBIRTH      => 'Cosmic Rebirth',
        };
    }
}
