<?php

namespace App\Domains\Reader;

/**
 * ReactionProcessor - Process reader emotional reactions
 * 
 * Maps reactions to WorldState deltas.
 */
class ReactionProcessor
{
    /**
     * Reaction weights (how much each reaction affects state).
     */
    private const REACTION_WEIGHTS = [
        'support' => 0.1,
        'oppose' => -0.1,
        'sadness' => 0.05,  // Increases trauma awareness
        'anger' => 0.05,    // Increases grievance
        'hope' => -0.05,    // Reduces trauma
    ];

    /**
     * Process reactions into WorldState deltas.
     * 
     * @param array $reactions Reader reactions
     * @param int $epoch Current epoch
     * @return array Deltas
     */
    public function process(array $reactions, int $epoch): array
    {
        if (empty($reactions)) {
            return [];
        }

        $deltas = [];

        // Count reactions by type
        $reactionCounts = [];
        foreach ($reactions as $reaction) {
            $type = $reaction['reaction_type'];
            $reactionCounts[$type] = ($reactionCounts[$type] ?? 0) + 1;
        }

        // Calculate total reactions
        $total = array_sum($reactionCounts);

        // Map reactions to deltas
        foreach ($reactionCounts as $type => $count) {
            $weight = self::REACTION_WEIGHTS[$type] ?? 0;
            $percentage = $count / $total;
            $influence = $weight * $percentage;

            match($type) {
                'sadness' => $deltas['trauma_density'] = ($deltas['trauma_density'] ?? 0) + $influence,
                'anger' => $deltas['grievance_load'] = ($deltas['grievance_load'] ?? 0) + $influence,
                'hope' => $deltas['trauma_density'] = ($deltas['trauma_density'] ?? 0) + $influence,
                'support' => $deltas['legitimacy'] = ($deltas['legitimacy'] ?? 0) + $influence,
                'oppose' => $deltas['legitimacy'] = ($deltas['legitimacy'] ?? 0) + $influence,
                default => null,
            };
        }

        return $deltas;
    }

    /**
     * Get available reaction types.
     */
    public function getReactionTypes(): array
    {
        return array_keys(self::REACTION_WEIGHTS);
    }
}
