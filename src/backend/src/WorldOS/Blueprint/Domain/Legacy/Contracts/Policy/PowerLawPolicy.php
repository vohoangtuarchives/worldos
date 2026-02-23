<?php

namespace WorldOS\Blueprint\Domain\Legacy\Contracts\Policy;

interface PowerLawPolicy
{
    /**
     * Resolve the power value for a given entity based on the snapshot context.
     * The snapshot is passed as an array for immutability.
     */
    public function resolve(array $snapshot, string $entityId): float;
}
