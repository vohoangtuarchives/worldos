<?php

namespace Tuzy\Application\Material\Analytics;

use Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class MutationChainTracker
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Track mutation chains in a world.
     * 
     * @return array Mutation chains with from->to relationships
     */
    public function track(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        
        $chains = [];
        $mutations = [];

        foreach ($instances as $instance) {
            $mutationState = $instance->mutation_state ?? [];
            
            // Track mutations FROM this material
            if (isset($mutationState['mutated_to'])) {
                $from = $instance->material->code;
                $to = $mutationState['mutated_to'];
                $epoch = $mutationState['mutation_epoch'] ?? null;

                $mutations[] = [
                    'from' => $from,
                    'to' => $to,
                    'epoch' => $epoch,
                    'description' => $mutationState['pathway_description'] ?? null,
                ];

                // Build chain
                if (!isset($chains[$from])) {
                    $chains[$from] = [];
                }
                $chains[$from][] = $to;
            }

            // Track mutations TO this material
            if (isset($mutationState['mutated_from'])) {
                $from = $mutationState['mutated_from'];
                $to = $instance->material->code;

                if (!isset($chains[$from])) {
                    $chains[$from] = [];
                }
                if (!in_array($to, $chains[$from])) {
                    $chains[$from][] = $to;
                }
            }
        }

        return [
            'chains' => $chains,
            'mutations' => $mutations,
            'total_mutations' => count($mutations),
            'unique_sources' => count($chains),
        ];
    }

    /**
     * Get longest mutation chain.
     */
    public function getLongestChain(World $world): array
    {
        $data = $this->track($world);
        $chains = $data['chains'];

        $longestChain = [];
        $maxDepth = 0;

        foreach ($chains as $source => $targets) {
            $depth = $this->getChainDepth($source, $chains);
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
                $longestChain = $this->buildChain($source, $chains);
            }
        }

        return [
            'chain' => $longestChain,
            'depth' => $maxDepth,
        ];
    }

    private function getChainDepth(string $material, array $chains, int $depth = 0): int
    {
        if (!isset($chains[$material]) || empty($chains[$material])) {
            return $depth;
        }

        $maxChildDepth = $depth;
        foreach ($chains[$material] as $target) {
            $childDepth = $this->getChainDepth($target, $chains, $depth + 1);
            $maxChildDepth = max($maxChildDepth, $childDepth);
        }

        return $maxChildDepth;
    }

    private function buildChain(string $material, array $chains): array
    {
        $chain = [$material];

        if (isset($chains[$material]) && !empty($chains[$material])) {
            // Follow first target (simplified - could track all branches)
            $target = $chains[$material][0];
            $chain = array_merge($chain, $this->buildChain($target, $chains));
        }

        return $chain;
    }
}
