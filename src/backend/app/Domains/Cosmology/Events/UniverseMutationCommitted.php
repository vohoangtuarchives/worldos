<?php

namespace App\Domains\Cosmology\Events;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Planning\StoryOutcomeDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a story outcome mutation has been committed to Universe.
 * ForgeProjectionListener (phase 2) may react to update World material.
 */
class UniverseMutationCommitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $universeId,
        public StoryOutcomeDTO $outcome,
        public WorldStateVector $delta,
    ) {
    }
}
