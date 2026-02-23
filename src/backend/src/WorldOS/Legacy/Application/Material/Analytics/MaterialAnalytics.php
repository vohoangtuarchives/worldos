<?php

namespace WorldOS\Legacy\Application\Material\Analytics;

use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;
use Illuminate\Support\Collection;

class MaterialAnalytics
{
    private MaterialRepositoryInterface $repository;
    private ActivationRateCalculator $activationCalculator;
    private MutationChainTracker $mutationTracker;
    private ConflictPatternDetector $conflictDetector;

    public function __construct(
        MaterialRepositoryInterface $repository,
        ActivationRateCalculator $activationCalculator,
        MutationChainTracker $mutationTracker,
        ConflictPatternDetector $conflictDetector
    ) {
        $this->repository = $repository;
        $this->activationCalculator = $activationCalculator;
        $this->mutationTracker = $mutationTracker;
        $this->conflictDetector = $conflictDetector;
    }

    /**
     * Get comprehensive analytics for a world.
     */
    public function getWorldAnalytics(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);

        return [
            'activation_rates' => $this->getActivationRates($world),
            'mutation_chains' => $this->getMutationChains($world),
            'conflict_patterns' => $this->getConflictPatterns($world),
            'material_distribution' => $this->getMaterialDistribution($world),
            'lifecycle_breakdown' => $this->getLifecycleBreakdown($instances),
            'strength_distribution' => $this->getStrengthDistribution($instances),
            'top_materials' => $this->getTopMaterials($instances),
        ];
    }

    /**
     * Get activation rates over time.
     */
    public function getActivationRates(World $world): array
    {
        return $this->activationCalculator->calculate($world);
    }

    /**
     * Get mutation chains for materials.
     */
    public function getMutationChains(World $world): array
    {
        return $this->mutationTracker->track($world);
    }

    /**
     * Get conflict patterns between materials.
     */
    public function getConflictPatterns(World $world): array
    {
        return $this->conflictDetector->detect($world);
    }

    /**
     * Get material distribution by ontology and function.
     */
    public function getMaterialDistribution(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        
        $distribution = [
            'by_ontology' => [],
            'by_function' => [],
        ];

        foreach ($instances as $instance) {
            $material = $instance->material;
            
            $ontology = $material->ontology->value;
            $function = $material->function->value;

            $distribution['by_ontology'][$ontology] = ($distribution['by_ontology'][$ontology] ?? 0) + 1;
            $distribution['by_function'][$function] = ($distribution['by_function'][$function] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Get lifecycle breakdown.
     */
    private function getLifecycleBreakdown(Collection $instances): array
    {
        $breakdown = [
            'dormant' => 0,
            'active' => 0,
            'retired' => 0,
        ];

        foreach ($instances as $instance) {
            if ($instance->retired_at) {
                $breakdown['retired']++;
            } elseif ($instance->activation_epoch !== null) {
                $breakdown['active']++;
            } else {
                $breakdown['dormant']++;
            }
        }

        return $breakdown;
    }

    /**
     * Get strength distribution histogram.
     */
    private function getStrengthDistribution(Collection $instances): array
    {
        $buckets = [
            '0-2' => 0,
            '3-5' => 0,
            '6-8' => 0,
            '9-10' => 0,
        ];

        foreach ($instances as $instance) {
            $strength = $instance->strength_level;
            
            if ($strength <= 2) {
                $buckets['0-2']++;
            } elseif ($strength <= 5) {
                $buckets['3-5']++;
            } elseif ($strength <= 8) {
                $buckets['6-8']++;
            } else {
                $buckets['9-10']++;
            }
        }

        return $buckets;
    }

    /**
     * Get top materials by strength.
     */
    private function getTopMaterials(Collection $instances, int $limit = 10): array
    {
        return $instances
            ->sortByDesc('strength_level')
            ->take($limit)
            ->map(fn($i) => [
                'code' => $i->material->code,
                'strength' => $i->strength_level,
                'ontology' => $i->material->ontology->value,
                'function' => $i->material->function->value,
            ])
            ->values()
            ->toArray();
    }
}
