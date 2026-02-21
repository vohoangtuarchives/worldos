<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Event;

/**
 * Universe (runtime instance) was ticked. SagaContext may react (narrative, branch scoring).
 */
readonly class UniverseTicked
{
    public function __construct(
        public string $universeId,
        public ?string $worldId,
        public int $age,
        public array $stateSummary = [],
    ) {
    }
}
