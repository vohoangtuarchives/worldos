<?php

namespace App\Domains\Material\Engine;

use Illuminate\Support\Collection;
use App\Domains\Material\MaterialInstance;

/**
 * CompatibilityGate - Component 3 of MaterialLawEngine (Enhanced)
 * 
 * Purpose: Resolve conflicts between incompatible materials.
 * Strategies: Mutation, Suppression, Explosion
 */
class CompatibilityGate
{
    /**
     * Check and resolve compatibility conflicts.
     * 
     * @param Collection $activeMaterials Currently active instances
     * @param Collection $newlyActivated Just activated instances
     * @return array Resolutions
     */
    public function resolve(Collection $activeMaterials, Collection $newlyActivated): array
    {
        $resolutions = [];

        foreach ($newlyActivated as $newInstance) {
            $conflicts = $this->detectConflicts($newInstance, $activeMaterials);
            
            foreach ($conflicts as $conflict) {
                $resolution = $this->resolveConflict($newInstance, $conflict);
                if ($resolution) {
                    $resolutions[] = $resolution;
                }
            }
        }

        return $resolutions;
    }

    /**
     * Detect conflicts for a newly activated material.
     */
    private function detectConflicts(MaterialInstance $newInstance, Collection $activeMaterials): array
    {
        $conflicts = [];
        $incompatibleWith = $newInstance->material->incompatible_with ?? [];

        if (empty($incompatibleWith)) {
            return $conflicts;
        }

        foreach ($activeMaterials as $activeInstance) {
            if (in_array($activeInstance->material->code, $incompatibleWith)) {
                $conflicts[] = $activeInstance;
            }
        }

        return $conflicts;
    }

    /**
     * Resolve a single conflict using one of three strategies.
     */
    private function resolveConflict(MaterialInstance $newInstance, MaterialInstance $conflictInstance): ?array
    {
        $newStrength = $newInstance->strength_level;
        $conflictStrength = $conflictInstance->strength_level;

        // Strategy 1: Mutation (if strength difference is moderate)
        if (abs($newStrength - $conflictStrength) <= 3) {
            return $this->resolveMutation($newInstance, $conflictInstance);
        }

        // Strategy 2: Suppression (if one is significantly stronger)
        if ($newStrength > $conflictStrength + 3) {
            return $this->resolveSuppression($conflictInstance, $newInstance);
        }

        if ($conflictStrength > $newStrength + 3) {
            return $this->resolveSuppression($newInstance, $conflictInstance);
        }

        // Strategy 3: Explosion (if both are very strong)
        if ($newStrength >= 7 && $conflictStrength >= 7) {
            return $this->resolveExplosion($newInstance, $conflictInstance);
        }

        // Default: suppress weaker
        return $this->resolveSuppression(
            $newStrength < $conflictStrength ? $newInstance : $conflictInstance,
            $newStrength >= $conflictStrength ? $newInstance : $conflictInstance
        );
    }

    /**
     * Mutation: Transform weaker material to compatible variant.
     */
    private function resolveMutation(MaterialInstance $weaker, MaterialInstance $stronger): array
    {
        $delta = -0.3; // Reduce strength

        return [
            'type' => 'mutation',
            'source' => $weaker->material->code,
            'target' => $this->findMutationTarget($weaker),
            'delta' => $delta,
            'reason' => "Conflict with {$stronger->material->code}",
            'instance_id' => $weaker->id,
        ];
    }

    /**
     * Suppression: Reduce strength of weaker material.
     */
    private function resolveSuppression(MaterialInstance $weaker, MaterialInstance $stronger): array
    {
        $delta = -0.4;

        return [
            'type' => 'suppression',
            'source' => $weaker->material->code,
            'suppressed_by' => $stronger->material->code,
            'delta' => $delta,
            'reason' => "Suppressed by stronger {$stronger->material->code}",
            'instance_id' => $weaker->id,
        ];
    }

    /**
     * Explosion: Both materials collapse, emit strong legacy.
     */
    private function resolveExplosion(MaterialInstance $first, MaterialInstance $second): array
    {
        return [
            'type' => 'explosion',
            'materials' => [$first->material->code, $second->material->code],
            'delta' => -0.8, // Severe collapse
            'reason' => 'Irreconcilable conflict between strong materials',
            'instance_ids' => [$first->id, $second->id],
            'legacy_strength' => 0.9, // Strong legacy from explosion
        ];
    }

    /**
     * Find mutation target for a material.
     * This would ideally use mutation_axes from material definition.
     */
    private function findMutationTarget(MaterialInstance $instance): string
    {
        $mutationAxes = $instance->material->mutation_axes ?? [];

        if (empty($mutationAxes)) {
            return $instance->material->code . '_VARIANT';
        }

        // Use first mutation axis as target
        return $instance->material->code . '_' . strtoupper($mutationAxes[0]);
    }

    /**
     * Legacy compatibility check (from old CompatibilityChecker).
     */
    public function isCompatible(MaterialInstance $instance, array $worldContext): bool
    {
        $material = $instance->material;

        // 1. Institutional + Stabilizing needs minimum tech/organization level
        if ($material->ontology === \Tuzy\Domain\Material\Enums\MaterialOntology::INSTITUTIONAL &&
            $material->function === \Tuzy\Domain\Material\Enums\MaterialFunction::STABILIZING) {
            
            $techLevel = $worldContext['tech_level'] ?? 0;
            if ($techLevel < 2) {
                return false;
            }
        }

        // 2. Explicit Incompatibility Check
        if ($material->incompatible_with) {
            foreach ($material->incompatible_with as $conflictCode) {
                if (in_array($conflictCode, $worldContext['active_materials'] ?? [])) {
                    return false;
                }
            }
        }

        return true;
    }
}
