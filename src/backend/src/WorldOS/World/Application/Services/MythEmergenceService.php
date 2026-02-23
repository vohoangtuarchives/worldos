<?php

namespace WorldOS\World\Application\Services;

use App\Models\World;
use App\Models\WorldBelief;
use App\Models\WorldMyth;

class MythEmergenceService
{
    public const EMERGENCE_THRESHOLD = 3;

    public function __construct(
        protected WorldLawValidator $validator
    ) {
    }

    public function check(World $world): void
    {
        $beliefs = WorldBelief::where('world_id', $world->id)
            ->where('repeat_count', '>=', self::EMERGENCE_THRESHOLD)
            ->get();

        foreach ($beliefs as $belief) {
            if ($belief->myths()->exists()) {
                continue;
            }
            if (!$this->validator->validateMythEmergence($world->law_profile, $belief->intensity)) {
                continue;
            }
            $myth = WorldMyth::create([
                'world_id' => $world->id,
                'name' => $belief->content,
                'strength' => $belief->intensity,
            ]);
            $myth->beliefs()->attach($belief);
        }
    }
}
