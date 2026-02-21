<?php

namespace Tuzy\Domain\World\Contracts\Policy;

interface ResourcePolicy
{
    /**
     * Calculate available resources or resource regeneration.
     */
    public function calculateRegeneration(array $snapshot, string $entityId): array;
}
