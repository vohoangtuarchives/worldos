<?php

namespace Tuzy\Domain\World\Policy;

use Tuzy\Domain\World\Contracts\Policy\ResourcePolicy;

class InfiniteResourcePolicy implements ResourcePolicy
{
    public function calculateRegeneration(array $snapshot, string $entityId): array
    {
        return ['mana' => 9999, 'stamina' => 9999];
    }
}
