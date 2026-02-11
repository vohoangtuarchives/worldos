<?php

namespace App\Domains\World\Contracts\Policy;

interface ResourcePolicy
{
    /**
     * Calculate available resources or resource regeneration.
     */
    public function calculateRegeneration(array $snapshot, string $entityId): array;
}
