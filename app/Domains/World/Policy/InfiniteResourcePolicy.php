<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\ResourcePolicy;

class InfiniteResourcePolicy implements ResourcePolicy
{
    public function calculateRegeneration(array $snapshot, string $entityId): array
    {
        return ['mana' => 9999, 'stamina' => 9999];
    }
}
