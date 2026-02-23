<?php

namespace WorldOS\Legacy\Application\CognitiveKernel\Mutation;

use WorldOS\Legacy\Domain\CognitiveKernel\Archetype;
use WorldOS\Legacy\Domain\CognitiveKernel\ArchetypeMutation;

/**
 * Mutation Executor
 * 
 * Executes archetype mutation (fork) when triggered
 * 
 * Constitutional Constraints:
 * - Mutation = fork only, never delete original
 * - Irreversible
 * - Creates 2 divergent variants
 * - Spreads slowly
 */
class MutationExecutor
{
    /**
     * Execute mutation (fork archetype into 2 variants)
     * 
     * @return ArchetypeMutation
     */
    public function execute(MutationTrigger $trigger): ArchetypeMutation
    {
        $parent = $trigger->archetype;

        // Mark original as mutated (but never delete)
        // Original archetype remains in system

        // Determine polarity split based on trigger type
        $variants = $this->determineVariants($parent, $trigger);

        // Create mutation record
        $mutation = ArchetypeMutation::create([
            'parent_archetype' => $parent->key,
            'variant_1' => $variants['variant_1'],
            'variant_2' => $variants['variant_2'],
            'trigger_type' => $trigger->type,
            'trigger_context' => $trigger->context,
            'origin_world_id' => $trigger->context['world_id'] ?? null,
            'origin_saga_id' => $trigger->context['saga_id'] ?? null,
            'irreversible' => true,
        ]);

        // Create variant archetypes in database
        $this->createVariantArchetypes($parent, $variants, $mutation);

        return $mutation;
    }

    /**
     * Determine variant names based on parent and trigger
     */
    private function determineVariants(Archetype $parent, MutationTrigger $trigger): array
    {
        // Get polarity axes from parent
        $polarity = $parent->polarity;

        if (count($polarity) < 2) {
            // If parent has single polarity, create conceptual split
            $polarity = $this->inferPolaritySplit($parent);
        }

        // Create variant suffix based on polarity
        $variant1Suffix = $polarity[0];
        $variant2Suffix = $polarity[1];

        return [
            'variant_1' => "{$parent->key}_{$variant1Suffix}",
            'variant_2' => "{$parent->key}_{$variant2Suffix}",
            'polarity_1' => [$polarity[0]],
            'polarity_2' => [$polarity[1]],
        ];
    }

    /**
     * Infer polarity split if not explicit
     */
    private function inferPolaritySplit(Archetype $parent): array
    {
        // Common polarity splits by domain
        return match($parent->domain) {
            Archetype::DOMAIN_POWER => ['order', 'chaos'],
            Archetype::DOMAIN_SOCIAL => ['unity', 'division'],
            Archetype::DOMAIN_PERCEPTION => ['clarity', 'ambiguity'],
            Archetype::DOMAIN_METAPHYSICAL => ['permanence', 'change'],
            default => ['positive', 'negative'],
        };
    }

    /**
     * Create variant archetypes in database
     */
    private function createVariantArchetypes(
        Archetype $parent,
        array $variants,
        ArchetypeMutation $mutation
    ): void {
        // Variant 1
        Archetype::create([
            'key' => $variants['variant_1'],
            'domain' => $parent->domain,
            'polarity' => $variants['polarity_1'],
            'baseline_weight' => $parent->baseline_weight * 0.6,
            'volatility' => $parent->volatility,
            'version' => $parent->version,
            'description' => "{$parent->description} [Mutated variant 1 from {$parent->key}]"
        ]);

        // Variant 2
        Archetype::create([
            'key' => $variants['variant_2'],
            'domain' => $parent->domain,
            'polarity' => $variants['polarity_2'],
            'baseline_weight' => $parent->baseline_weight * 0.4,
            'volatility' => $parent->volatility,
            'version' => $parent->version,
            'description' => "{$parent->description} [Mutated variant 2 from {$parent->key}]"
        ]);
    }

    /**
     * Check if mutation is irreversible (always true)
     */
    public function isIrreversible(ArchetypeMutation $mutation): bool
    {
        return true; // Constitutional constraint: mutations never reverse
    }
}
