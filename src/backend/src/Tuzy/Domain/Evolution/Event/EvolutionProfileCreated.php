<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Event;

final class EvolutionProfileCreated
{
    public function __construct(
        public readonly string $profileId,
        public readonly string $profileName,
    ) {
    }
}
