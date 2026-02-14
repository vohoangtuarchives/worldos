<?php

namespace App\Domains\Saga;

use App\Domains\CognitiveKernel\ArchetypeWeight;

/**
 * Myth Legacy Extractor
 * 
 * Extracts archetype and myth legacy from completed worlds.
 * 
 * Legacy Types:
 * 1. Archetype Legacy - Which archetypes were dominant/suppressed
 * 2. Myth Legacy - Residue from collapsed myths
 */
class MythLegacyExtractor
{
    /**
     * Extract legacy from a saga world
     */
    public function extract(SagaWorld $sagaWorld): array
    {
        $world = $sagaWorld->world;

        return [
            'archetype_legacy' => $this->extractArchetypeLegacy($world),
            'myth_legacy' => $this->extractMythLegacy($world),
            'trauma_legacy' => $this->extractTraumaLegacy($world),
        ];
    }

    /**
     * Extract archetype legacy
     * 
     * Returns archetypes that were significant (very high or very low weight)
     */
    private function extractArchetypeLegacy($world): array
    {
        $weights = ArchetypeWeight::where('world_id', $world->id)->get();

        $legacy = [];

        foreach ($weights as $weight) {
            // High weight archetypes leave positive residue
            if ($weight->weight > 0.7) {
                $legacy[$weight->archetype_key] = [
                    'type' => 'dominance',
                    'intensity' => $weight->weight,
                    'bias' => 0.1, // Bias for next world
                ];
            }

            // Suppressed archetypes create pendulum pressure
            if ($weight->weight < 0.2) {
                $legacy[$weight->archetype_key] = [
                    'type' => 'suppression',
                    'intensity' => 1 - $weight->weight,
                    'bias' => -0.1, // Negative bias (will swing back)
                ];
            }
        }

        return $legacy;
    }

    /**
     * Extract myth legacy
     * 
     * Strong myths leave interpretive residue
     */
    private function extractMythLegacy($world): array
    {
        $myths = $world->myths()
            ->where('rigidity', '>', 0.5)
            ->get();

        return $myths->map(function ($myth) {
            return [
                'doctrine' => $myth->truth_statement,
                'strength' => $myth->rigidity,
                'archetypes' => [],
                'residue_type' => $this->determinResidueType($myth),
            ];
        })->toArray();
    }

    /**
     * Extract trauma legacy
     * 
     * Scars create long-term bias
     */
    private function extractTraumaLegacy($world): array
    {
        $scars = $world->scars()->get();

        return $scars->map(function ($scar) {
            return [
                'trigger' => $scar->constraint_rule,
                'severity' => $scar->severity ?? 0.5,
                'related_archetypes' => [],
            ];
        })->toArray();
    }

    /**
     * Determine residue type from myth
     */
    private function determinResidueType($myth): string
    {
        // New schema uses rigidity as proxy for residue type
        $rigidity = $myth->rigidity ?? 0.5;

        return match(true) {
            $rigidity >= 0.9 => 'reverence',
            $rigidity >= 0.7 => 'melancholy',
            $rigidity <= 0.3 => 'trauma',
            default => 'neutral',
        };
    }
}
