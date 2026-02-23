<?php

namespace WorldOS\Blueprint\Domain\Legacy\Policy;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\PowerLawPolicy;

class LinearPowerPolicy implements PowerLawPolicy
{
    public function resolve(array $snapshot, string $entityId): float
    {
        $attributes = $snapshot['characters'][$entityId]['attributes'] ?? [];
        return (float) ($attributes['power_base'] ?? 0);
    }
}
