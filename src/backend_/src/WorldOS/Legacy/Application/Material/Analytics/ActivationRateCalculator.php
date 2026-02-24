<?php

namespace WorldOS\Legacy\Application\Material\Analytics;

use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class ActivationRateCalculator
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Calculate activation rates per epoch.
     * 
     * @return array [epoch => activation_count]
     */
    public function calculate(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        
        $activationsByEpoch = [];
        $currentEpoch = $world->tick;

        // Group activations by epoch
        foreach ($instances as $instance) {
            if ($instance->activation_epoch !== null) {
                $epoch = $instance->activation_epoch;
                $activationsByEpoch[$epoch] = ($activationsByEpoch[$epoch] ?? 0) + 1;
            }
        }

        // Fill in missing epochs with 0
        for ($i = 0; $i <= $currentEpoch; $i++) {
            if (!isset($activationsByEpoch[$i])) {
                $activationsByEpoch[$i] = 0;
            }
        }

        ksort($activationsByEpoch);

        return [
            'by_epoch' => $activationsByEpoch,
            'total_activations' => array_sum($activationsByEpoch),
            'average_per_epoch' => $currentEpoch > 0 ? array_sum($activationsByEpoch) / $currentEpoch : 0,
            'peak_epoch' => array_keys($activationsByEpoch, max($activationsByEpoch))[0] ?? null,
        ];
    }

    /**
     * Calculate activation rate as percentage of total materials.
     */
    public function getActivationPercentage(World $world): float
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $total = $instances->count();
        
        if ($total === 0) {
            return 0;
        }

        $activated = $instances->filter(fn($i) => $instance->activation_epoch !== null)->count();

        return ($activated / $total) * 100;
    }
}
