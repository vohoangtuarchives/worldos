<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

/**
 * Represents a bifurcation / branch in world trajectory.
 * Emitted when BifurcationAnalyzer detects dynamic stress above threshold.
 */
readonly class BranchEvent
{
    public function __construct(
        public string $type,
        public string $reason,
        public float $chaosIndex,
        public array $metadata = [],
    ) {
    }
}
