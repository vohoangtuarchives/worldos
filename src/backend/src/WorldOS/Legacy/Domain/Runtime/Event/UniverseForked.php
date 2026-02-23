<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Runtime\Event;

/**
 * Universe was forked (new branch). SagaContext may react.
 */
readonly class UniverseForked
{
    public function __construct(
        public string $sourceUniverseId,
        public string $newUniverseId,
        public ?string $worldId,
        public array $payload = [],
    ) {
    }
}
