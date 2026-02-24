<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service\MetaCosmos;

use WorldOS\Evolution\Domain\Legacy\Entity\LawGenome;
use WorldOS\Evolution\Domain\Legacy\Entity\Universe;
use WorldOS\Evolution\Domain\Legacy\Service\Fitness\MutationEngine;
use WorldOS\Legacy\Domain\Cosmos\Service\ObjectiveEngine;
use WorldOS\Legacy\Domain\Cosmos\Service\ParetoSelector;

class MetaCycleOrchestrator
{
    private ObjectiveEngine $objectiveEngine;
    private ParetoSelector $selector;
    private MutationEngine $mutationEngine;

    public function __construct(
        ObjectiveEngine $objectiveEngine,
        ParetoSelector $selector,
        MutationEngine $mutationEngine
    ) {
        $this->objectiveEngine = $objectiveEngine;
        $this->selector = $selector;
        $this->mutationEngine = $mutationEngine;
    }

    public function getObjectiveEngine(): ObjectiveEngine
    {
        return $this->objectiveEngine;
    }

    /**
     * Executes one Meta-Cycle for a specific LawGenome.
     * @param Universe[] $activeUniverses
     * @return array {nextGeneration: Universe[], evaluation: array}
     */
    public function runCycle(LawGenome $law, array $activeUniverses): array
    {
        // 1. Evaluate current generation (Get FitnessVectors)
        $scoredUniverses = $this->evaluateAll($activeUniverses);

        // 2. Selection (Pareto-based survivors)
        $targetSurvivors = (int) ceil(count($activeUniverses) / 2);
        $survivors = $this->selector->select($scoredUniverses, $targetSurvivors);

        // 3. Reproduce & Mutate to fill population
        $nextGeneration = [];
        $targetPopulation = count($activeUniverses);

        // Keep survivors (Elite)
        foreach ($survivors as $survivorData) {
            $nextGeneration[] = $survivorData['universe'];
        }

        // Fill remaining slots with mutated offspring of survivors
        while (count($nextGeneration) < $targetPopulation) {
            $parentData = $survivors[array_rand($survivors)];
            $parent = $parentData['universe'];
            
            $nextGeneration[] = $this->spawnOffspring($parent, $law);
        }

        return [
            'nextGeneration' => $nextGeneration,
            'evaluation' => $scoredUniverses // Return data for CLI/UI display
        ];
    }

    private function evaluateAll(array $universes): array
    {
        $evaluation = [];
        foreach ($universes as $universe) {
            $civilizations = []; // TODO: Load from repository 
            
            // Now using ObjectiveEngine to get a multi-dimensional FitnessVector
            $vector = $this->objectiveEngine->evaluate($universe, $civilizations);
            
            $evaluation[] = [
                'universe' => $universe,
                'vector' => $vector
            ];
        }

        return $evaluation;
    }

    private function spawnOffspring(Universe $parent, LawGenome $law): Universe
    {
        // Future: Parent's LawGenome/Constants mutation
        return new Universe(
            id: uniqid('uni_'),
            lawGenome: $law,
            year: 0
        );
    }
}
