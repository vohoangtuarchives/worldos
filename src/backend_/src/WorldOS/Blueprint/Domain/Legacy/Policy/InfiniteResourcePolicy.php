<?php

namespace WorldOS\Blueprint\Domain\Legacy\Policy;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ResourcePolicy;

class InfiniteResourcePolicy implements ResourcePolicy
{
    public function calculateRegeneration(array $snapshot, string $entityId): array
    {
        return ['mana' => 9999, 'stamina' => 9999];
    }
}
