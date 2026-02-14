<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\PowerLawPolicy;

class LinearPowerPolicy implements PowerLawPolicy
{
    public function resolve(array $snapshot, string $entityId): float
    {
        $attributes = $snapshot['characters'][$entityId]['attributes'] ?? [];
        return (float) ($attributes['power_base'] ?? 0);
    }
}
