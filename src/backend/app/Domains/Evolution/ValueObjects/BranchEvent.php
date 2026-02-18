<?php

declare(strict_types=1);

namespace App\Domains\Evolution\ValueObjects;

/**
 * BranchEvent - Represents a bifurcation / branch in world trajectory.
 * Emitted when BifurcationAnalyzer detects dynamic stress (curvature, divergence) above threshold.
 */
final class BranchEvent
{
    public function __construct(
        public readonly string $type,
        public readonly string $reason,
        public readonly float $chaosIndex,
        public readonly array $metadata = []
    ) {
    }
}
