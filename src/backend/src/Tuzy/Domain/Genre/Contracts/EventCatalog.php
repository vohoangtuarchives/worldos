<?php

namespace Tuzy\Domain\Genre\Contracts;

interface EventCatalog
{
    /**
     * Get list of allowed event types.
     * e.g., ['combat', 'tribulation', 'auction']
     */
    public function allowedEvents(): array;
    
    /**
     * Get rules/frequency for a specific event type.
     */
    public function eventRules(string $eventType): array;
}
