<?php

namespace WorldOS\Legacy\Application\CognitiveKernel\Mutation;

use WorldOS\Legacy\Domain\CognitiveKernel\Archetype;
use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypeWeight;
use App\Models\World;

/**
 * Mutation Detector
 * 
 * Detects when archetypes should mutate (fork) based on 3 constitutional triggers:
 * 1. Extreme Civilization Collapse
 * 2. Myth Paradox
 * 3. Repeated Failure Across Sagas
 * 
 * Constitutional Constraint (ADR-1002, ARCHETYPE_MUTATION.md):
 * Only these 3 triggers are allowed. No more.
 */
class MutationDetector
{
    /**
     * Check if mutation should occur for an archetype
     * 
     * @return MutationTrigger|null
     */
    public function detect(
        Archetype $archetype,
        World $world,
        ?array $sagaHistory = null
    ): ?MutationTrigger {
        // Trigger 1: Extreme Collapse
        if ($this->detectExtremeCollapse($world, $archetype)) {
            return new MutationTrigger(
                type: MutationTrigger::EXTREME_COLLAPSE,
                archetype: $archetype,
                context: [
                    'world_id' => $world->id,
                   'legitimacy' => $this->calculateLegitimacy($world),
                    'collapse_severity' => $this->getCollapseSeverity($world)
                ]
            );
        }

        // Trigger 2: Myth Paradox
        if ($this->detectMythParadox($world, $archetype)) {
            return new MutationTrigger(
                type: MutationTrigger::MYTH_PARADOX,
                archetype: $archetype,
                context: [
                    'world_id' => $world->id,
                    'conflicting_myths' => $this->getConflictingMyths($world, $archetype)
                ]
            );
        }

        // Trigger 3: Repeated Failure Across Sagas
        if ($sagaHistory && $this->detectRepeatedFailure($archetype, $sagaHistory)) {
            return new MutationTrigger(
                type: MutationTrigger::REPEATED_FAILURE,
                archetype: $archetype,
                context: [
                    'failure_count' => $this->countFailures($archetype, $sagaHistory),
                    'saga_ids' => $this->getFailedSagaIds($archetype, $sagaHistory)
                ]
            );
        }

        return null;
    }

    /**
     * Trigger 1: Extreme Civilization Collapse
     */
    private function detectExtremeCollapse(World $world, Archetype $archetype): bool
    {
        // Check conditions:
        // - legitimacy = 0
        // - economy collapsed (if exists)
        // - myths using this archetype all ineffective

        $legitimacy = $this->calculateLegitimacy($world);
        
        if ($legitimacy > 0.1) {
            return false; // Not extreme enough
        }

        // Check if myths using this archetype are ineffective
        $myths = $world->myths()
            ->whereJsonContains('metadata->archetypes', $archetype->key)
            ->get();

        if ($myths->isEmpty()) {
            return false;
        }

        $allIneffective = $myths->every(function ($myth) {
            return ($myth->strength ?? 0) < 0.2;
        });

        return $allIneffective;
    }

    /**
     * Trigger 2: Myth Paradox
     */
    private function detectMythParadox(World $world, Archetype $archetype): bool
    {
        // Check if 2+ myths using same archetype lead to contradictory morals
        $myths = $world->myths()
            ->whereJsonContains('metadata->archetypes', $archetype->key)
            ->get();

        if ($myths->count() < 2) {
            return false;
        }

        // Check for moral contradiction
        $moralStances = $myths->map(function ($myth) {
            return $myth->metadata['moral_stance'] ?? 'neutral';
        })->unique();

        // If we have opposing moral stances (e.g., "good" vs "evil") on same archetype
        $hasContradiction = $moralStances->contains('good') && $moralStances->contains('evil');

        return $hasContradiction;
    }

    /**
     * Trigger 3: Repeated Failure Across Sagas
     */
    private function detectRepeatedFailure(Archetype $archetype, array $sagaHistory): bool
    {
        $failures = $this->countFailures($archetype, $sagaHistory);
        
        // 3 or more collapses with same archetype across different sagas
        return $failures >= 3;
    }

    /**
     * Helper: Calculate world legitimacy
     */
    private function calculateLegitimacy(World $world): float
    {
        // Simplified legitimacy calculation
        // In full implementation, use CouplingRules
        $archetypeWeights = ArchetypeWeight::where('world_id', $world->id)->get();
        $avgWeight = $archetypeWeights->avg('weight') ?? 0.5;
        
        $mythIntensity = $world->myths()->avg('strength') ?? 0.5;
        
        // Simple formula
        return $avgWeight * $mythIntensity;
    }

    /**
     * Helper: Get collapse severity
     */
    private function getCollapseSeverity(World $world): float
    {
        $scarsCount = $world->scars()->count();
        return min(1.0, $scarsCount / 10);
    }

    /**
     * Helper: Get conflicting myths
     */
    private function getConflictingMyths(World $world, Archetype $archetype): array
    {
        return $world->myths()
            ->whereJsonContains('metadata->archetypes', $archetype->key)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'doctrine' => $m->doctrine,
                'moral_stance' => $m->metadata['moral_stance'] ?? 'neutral'
            ])
            ->toArray();
    }

    /**
     * Helper: Count failures for archetype in saga history
     */
    private function countFailures(Archetype $archetype, array $sagaHistory): int
    {
        $count = 0;
        
        foreach ($sagaHistory as $saga) {
            $collapses = $saga['collapses'] ?? [];
            
            foreach ($collapses as $collapse) {
                if (($collapse['dominant_archetype'] ?? '') === $archetype->key) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Helper: Get failed saga IDs
     */
    private function getFailedSagaIds(Archetype $archetype, array $sagaHistory): array
    {
        $sagaIds = [];
        
        foreach ($sagaHistory as $saga) {
            $collapses = $saga['collapses'] ?? [];
            
            foreach ($collapses as $collapse) {
                if (($collapse['dominant_archetype'] ?? '') === $archetype->key) {
                    $sagaIds[] = $saga['id'];
                    break; // Only count each saga once
                }
            }
        }
        
        return $sagaIds;
    }
}
