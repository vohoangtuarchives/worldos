<?php

namespace Tuzy\Application\Historian\Queries;

use Tuzy\Domain\Saga\Saga;

/**
 * Divergence Queries
 * 
 * Constitutional Queries:
 * ✅ ALLOWED: "What could have been different?"
 * ✅ ALLOWED: "Where did paths diverge?"
 * ❌ FORBIDDEN: "What was the correct choice?"
 */
class DivergenceQueries
{
    /**
     * Where did paths diverge?
     * 
     * Compare two worlds in saga with similar setup but different outcomes
     */
    public function whereDidPathsDiverge(Saga $saga, int $world1Seq, int $world2Seq): array
    {
        $world1 = $saga->sagaWorlds()->where('sequence', $world1Seq)->first();
        $world2 = $saga->sagaWorlds()->where('sequence', $world2Seq)->first();

        if (!$world1 || !$world2) {
            return ['error' => 'Worlds not found'];
        }

        return [
            'world_1' => [
                'sequence' => $world1->sequence,
                'status' => $world1->status,
                'archetype_legacy' => $world1->archetype_legacy,
            ],
            'world_2' => [
                'sequence' => $world2->sequence,
                'status' => $world2->status,
                'archetype_legacy' => $world2->archetype_legacy,
            ],
            'divergence_points' => $this->identifyDivergencePoints($world1, $world2),
        ];
    }

    /**
     * Identify divergence points between two worlds
     */
    private function identifyDivergencePoints($world1, $world2): array
    {
        $divergences = [];

        // Different outcomes?
        if ($world1->status !== $world2->status) {
            $divergences[] = [
                'type' => 'outcome',
                'world_1' => $world1->status,
                'world_2' => $world2->status,
            ];
        }

        // Different archetype dominance?
        $legacy1 = $world1->archetype_legacy ?? [];
        $legacy2 = $world2->archetype_legacy ?? [];

        $archetypes1 = array_keys($legacy1);
        $archetypes2 = array_keys($legacy2);

        $unique1 = array_diff($archetypes1, $archetypes2);
        $unique2 = array_diff($archetypes2, $archetypes1);

        if (!empty($unique1) || !empty($unique2)) {
            $divergences[] = [
                'type' => 'archetype_dominance',
                'unique_to_world_1' => $unique1,
                'unique_to_world_2' => $unique2,
            ];
        }

        return $divergences;
    }

    /**
     * What counterfactual scenarios exist?
     */
    public function whatCounterfactualsExist(Saga $saga): array
    {
        $collapsed = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->get();

        $survived = $saga->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COMPLETED)
            ->get();

        if ($collapsed->isEmpty() || $survived->isEmpty()) {
            return ['note' => 'Need both collapsed and survived worlds for counterfactuals'];
        }

        $counterfactuals = [];

        // "What if collapsed world had archetype X?"
        foreach ($collapsed as $collapsedWorld) {
            $collapsedArchetypes = array_keys($collapsedWorld->archetype_legacy ?? []);

            foreach ($survived as $survivedWorld) {
                $survivedArchetypes = array_keys($survivedWorld->archetype_legacy ?? []);

                $missingInCollapsed = array_diff($survivedArchetypes, $collapsedArchetypes);

                if (!empty($missingInCollapsed)) {
                    $counterfactuals[] = [
                        'question' => "What if world #{$collapsedWorld->sequence} had " . implode(', ', $missingInCollapsed) . "?",
                        'collapsed_world' => $collapsedWorld->sequence,
                        'survived_model' => $survivedWorld->sequence,
                        'missing_archetypes' => $missingInCollapsed,
                    ];
                }
            }
        }

        return array_slice($counterfactuals, 0, 5); // Limit to 5 examples
    }
}
