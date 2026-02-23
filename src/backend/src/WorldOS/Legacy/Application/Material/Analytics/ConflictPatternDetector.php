<?php

namespace WorldOS\Legacy\Application\Material\Analytics;

use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class ConflictPatternDetector
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Detect conflict patterns in a world.
     * 
     * @return array Active conflicts and historical conflict frequency
     */
    public function detect(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $activeInstances = $instances->filter(fn($i) => $i->activation_epoch !== null && !$i->retired_at);

        $activeConflicts = [];
        $conflictFrequency = [];

        foreach ($activeInstances as $i => $instance1) {
            $incompatible = $instance1->material->incompatible_with ?? [];

            foreach ($activeInstances as $j => $instance2) {
                if ($i >= $j) continue; // Avoid duplicates

                if (in_array($instance2->material->code, $incompatible)) {
                    $pair = $this->sortPair($instance1->material->code, $instance2->material->code);
                    
                    $activeConflicts[] = [
                        'material1' => $pair[0],
                        'material2' => $pair[1],
                        'strength1' => $instance1->strength_level,
                        'strength2' => $instance2->strength_level,
                        'conflict_intensity' => $instance1->strength_level + $instance2->strength_level,
                    ];

                    // Track frequency
                    $pairKey = "{$pair[0]}+{$pair[1]}";
                    $conflictFrequency[$pairKey] = ($conflictFrequency[$pairKey] ?? 0) + 1;
                }
            }
        }

        return [
            'active_conflicts' => $activeConflicts,
            'conflict_count' => count($activeConflicts),
            'conflict_frequency' => $conflictFrequency,
            'most_common_conflict' => $this->getMostCommonConflict($conflictFrequency),
            'conflict_density' => $this->calculateConflictDensity($activeConflicts, $activeInstances->count()),
        ];
    }

    /**
     * Sort material pair alphabetically for consistent keys.
     */
    private function sortPair(string $a, string $b): array
    {
        return $a < $b ? [$a, $b] : [$b, $a];
    }

    /**
     * Get most common conflict pair.
     */
    private function getMostCommonConflict(array $frequency): ?array
    {
        if (empty($frequency)) {
            return null;
        }

        $maxFreq = max($frequency);
        $mostCommon = array_search($maxFreq, $frequency);

        [$material1, $material2] = explode('+', $mostCommon);

        return [
            'materials' => [$material1, $material2],
            'frequency' => $maxFreq,
        ];
    }

    /**
     * Calculate conflict density (conflicts per active material).
     */
    private function calculateConflictDensity(array $conflicts, int $activeMaterials): float
    {
        if ($activeMaterials === 0) {
            return 0;
        }

        return count($conflicts) / $activeMaterials;
    }
}
