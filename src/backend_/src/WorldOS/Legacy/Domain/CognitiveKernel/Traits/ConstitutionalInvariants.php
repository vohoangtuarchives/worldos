<?php

namespace WorldOS\Legacy\Domain\CognitiveKernel\Traits;

trait ConstitutionalInvariants
{
    /**
     * Boot the trait to enforce constitutional invariants.
     * ADR-1001: Cognitive Kernel Invariants (Immutable per major version)
     * ADR-1002: Archetype Lifecycle (No deletion)
     */
    protected static function bootConstitutionalInvariants()
    {
        // Enforce Immutability (ADR-1001)
        static::updating(function ($model) {
            // Allow updates to timestamps if they are the only thing changing
            if ($model->isDirty() && count($model->getDirty()) === 1 && $model->isDirty('updated_at')) {
                return true;
            }

            // In a real scenario, we might check for a specialized "KernelMigration" state here.
            // For now, we enforce strict immutability.
            throw new \RuntimeException(
                "Constitutional Violation (ADR-1001): Cognitive Kernel elements are immutable. " .
                "Cannot update " . class_basename($model) . " ID: " . $model->getKey()
            );
        });

        // Enforce Permanence (ADR-1002)
        static::deleting(function ($model) {
            throw new \RuntimeException(
                "Constitutional Violation (ADR-1002): Cognitive Kernel elements cannot be deleted. " .
                "Archetypes must be preserved for historical continuity."
            );
        });
    }
}
