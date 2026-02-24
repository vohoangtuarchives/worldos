<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service\Fitness;

use WorldOS\Evolution\Domain\Legacy\Entity\Universe;
use WorldOS\Evolution\Domain\Legacy\Entity\CivilizationState;

class UniverseFitnessEvaluator
{
    private CivilizationFitnessEvaluator $civEvaluator;

    public function __construct(CivilizationFitnessEvaluator $civEvaluator)
    {
        $this->civEvaluator = $civEvaluator;
    }

    /**
     * Evaluates the overall fitness of a Universe.
     * @param CivilizationState[] $civilizations
     */
    public function evaluate(Universe $universe, array $civilizations): float
    {
        // 1. Lineage Depth (History length vs Goal)
        // A universe that lasts longer and evolves more is better.
        $lineageScore = min(1.0, $universe->getYear() / 2000.0);

        // 2. Civilization Quality (Aggregate)
        $totalCivFitness = 0.0;
        foreach ($civilizations as $civ) {
            $totalCivFitness += $this->civEvaluator->evaluate($civ->getSnapshot());
        }
        $avgCivFitness = count($civilizations) > 0 ? $totalCivFitness / count($civilizations) : 0.0;

        // 3. Ideological Diversity (Collision Potential)
        $diversityScore = $this->calculateIdeologicalDiversity($civilizations);

        // 4. Scenario Complexity (Entropy Band vs Stability)
        // We want a universe that is on the edge of chaos but doesn't explode.
        $entropy = $universe->getCosmicState()->entropy;
        $maxEntropy = $universe->getLawGenome()->getMaxEntropy();
        // Peak fitness when entropy is near the threshold but hasn't breached it.
        $complexityScore = 1.0 - abs($entropy - $maxEntropy);

        // Final Universe Fitness
        $fitness = (
            $lineageScore * 0.20 +
            $avgCivFitness * 0.40 +
            $diversityScore * 0.20 +
            $complexityScore * 0.20
        );

        // Add small stochastic noise for realistic evolution drift in logs
        $noise = (mt_rand() / mt_getrandmax() * 0.05);
        
        return round($fitness + $noise, 4);
    }

    /**
     * @param CivilizationState[] $civilizations
     */
    private function calculateIdeologicalDiversity(array $civilizations): float
    {
        if (count($civilizations) < 2) return 0.0;

        $ideologySums = [0, 0, 0, 0, 0, 0];
        $count = 0;

        foreach ($civilizations as $civ) {
            foreach ($civ->getSnapshot()->factions as $faction) {
                $vector = $faction->ideology;
                $ideologySums[0] += $vector->centralization;
                $ideologySums[1] += $vector->economicControl;
                $ideologySums[2] += $vector->culturalOpenness;
                $ideologySums[3] += $vector->innovationBias;
                $ideologySums[4] += $vector->militarization;
                $ideologySums[5] += $vector->flexibility;
                $count++;
            }
        }

        if ($count === 0) return 0.0;

        $means = array_map(fn($sum) => $sum / $count, $ideologySums);
        $varianceSum = 0.0;

        foreach ($civilizations as $civ) {
            foreach ($civ->getSnapshot()->factions as $faction) {
                $vector = $faction->ideology;
                $varianceSum += pow($vector->centralization - $means[0], 2);
                $varianceSum += pow($vector->economicControl - $means[1], 2);
                $varianceSum += pow($vector->culturalOpenness - $means[2], 2);
                $varianceSum += pow($vector->innovationBias - $means[3], 2);
                $varianceSum += pow($vector->militarization - $means[4], 2);
                $varianceSum += pow($vector->flexibility - $means[5], 2);
            }
        }

        // Higher variance = Higher diversity
        return min(1.0, $varianceSum / ($count * 6));
    }
}
