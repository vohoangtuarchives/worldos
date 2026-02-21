<?php

declare(strict_types=1);

namespace App\Listeners;

use Tuzy\Domain\Cosmology\Events\UniverseMutationCommitted;

/**
 * Listens to UniverseMutationCommitted. Phase 2: apply World material updates
 * (institution, artifact, region) from structural shift. Forge does not call back to Universe.
 */
class ForgeProjectionListener
{
    /**
     * Handle the event. Placeholder for phase 2: ApplyWorldMaterialChanges.
     */
    public function handle(UniverseMutationCommitted $event): void
    {
        // Phase 2: map $event->delta to World material updates (institution dissolve,
        // artifact power change, region governance). No call back to Universe.
        // For now no-op; extend when Forge projection is implemented.
    }
}
