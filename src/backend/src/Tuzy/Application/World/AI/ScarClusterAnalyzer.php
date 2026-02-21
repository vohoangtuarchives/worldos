<?php

namespace Tuzy\Application\World\AI;

use App\Models\World;
use App\Models\WorldScar;

class ScarClusterAnalyzer
{
    /**
     * Detects if too many scars formed in a short period (last 100 ticks).
     * This signals a "Trauma Cluster" which makes the world very dark/heavy.
     */
    public function analyze(World $world): ?array
    {
        // For simplicity, just count total scars for now, 
        // in a real implementation we would check the time clustering.
        $recentScarCount = WorldScar::query()
            ->where('world_id', $world->id)
            ->count();

        if ($recentScarCount < 5) {
            return null;
        }

        return [
            'type' => 'scar_cluster',
            'content' => [
                'scar_count' => $recentScarCount,
                'cluster_density' => 'high',
            ],
            'suggestion' => "The world is becoming heavily scarred ($recentScarCount active scars). Consider a period of peace/inertia to allow for healing or 'forgetting'.",
        ];
    }
}
