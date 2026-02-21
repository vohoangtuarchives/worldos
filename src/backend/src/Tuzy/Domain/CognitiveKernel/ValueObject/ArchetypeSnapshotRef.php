<?php

declare(strict_types=1);

namespace Tuzy\Domain\CognitiveKernel\ValueObject;

/**
 * Reference to an archetype snapshot (version identifier).
 * Domain-only; no Eloquent.
 */
final readonly class ArchetypeSnapshotRef
{
    public function __construct(
        public string $version,
        public ?string $snapshotId = null,
    ) {
    }
}
