<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Event;

/**
 * Universe (runtime instance) collapsed. SagaContext may react (canonize, archive).
 */
readonly class UniverseCollapsed
{
    public function __construct(
        public string $universeId,
        public ?string $worldId,
        public string $cause,
        public array $finalState = [],
    ) {
    }
}
