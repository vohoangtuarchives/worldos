<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World;
use App\Models\WorldBelief;
use App\Models\WorldMyth;

class MythEmergenceService
{
    // A belief must be repeated this many times to become a Myth
    const EMERGENCE_THRESHOLD = 3;

    public function __construct(
        protected WorldLawValidator $validator
    ) {}

    /**
     * Scan the world for beliefs that are strong enough to emerge as Myths.
     * This is a deterministic process based on belief repetition.
     */
    public function check(World $world): void
    {
        // 1. Find beliefs that meet the threshold
        $beliefs = WorldBelief::where('world_id', $world->id)
            ->where('repeat_count', '>=', self::EMERGENCE_THRESHOLD)
            ->get();

        foreach ($beliefs as $belief) {
            // 2. Skip if already attached to a myth
            if ($belief->myths()->exists()) {
                continue;
            }

            // 3. ADR-0004: Validate against World Law
            if (!$this->validator->validateMythEmergence($world->law_profile, $belief->intensity)) {
                // Determine whether to delete or keep as belief is separate logic.
                // For now, we just don't promote it to Myth.
                continue;
            }

            // 4. Crystallize into a new Myth
            // The myth name is initially the belief content
            $myth = WorldMyth::create([
                'world_id' => $world->id,
                'name'     => $belief->content,
                'strength' => $belief->intensity,
            ]);

            // 5. Link belief to myth
            $myth->beliefs()->attach($belief);
        }
    }
}
