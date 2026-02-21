<?php

namespace Tuzy\Application\World\AI;

use App\Models\World;
use App\Models\WorldMyth;

class MythOvergrowthAnalyzer
{
    /**
     * Check if any myth is becoming too powerful (Strength > 50).
     * This signals a lack of ideological diversity or an oppressive narrative.
     */
    public function analyze(World $world): ?array
    {
        $dominantMyths = WorldMyth::query()
            ->where('world_id', $world->id)
            ->where('status', 'active')
            ->where('strength', '>', 50)
            ->get();

        if ($dominantMyths->isEmpty()) {
            return null;
        }

        $names = $dominantMyths->pluck('name')->join(', ');

        return [
            'type' => 'myth_overgrowth',
            'content' => [
                'dominant_myths' => $dominantMyths->toArray(),
                'risk_level' => 'high',
            ],
            'suggestion' => "Consider introducing a counter-belief or a 'schism' event to break the dominance of: {$names}.",
        ];
    }
}
