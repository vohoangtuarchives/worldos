<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Event;

/**
 * Material was injected into a World. RuntimeContext may react.
 */
readonly class MaterialInjected
{
    public function __construct(
        public string $worldId,
        public string $materialId,
        public array $payload = [],
    ) {
    }
}
