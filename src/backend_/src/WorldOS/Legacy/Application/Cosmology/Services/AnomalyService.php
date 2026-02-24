<?php

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

class AnomalyService
{
    const TYPE_VOID_STORM = 'VOID_STORM';
    const TYPE_GOLDEN_AGE = 'GOLDEN_AGE';
    const TYPE_GREAT_FILTER = 'GREAT_FILTER';

    /**
     * Return list of active (ongoing) anomalies for the universe.
     * For now returns empty array; can be extended to read from parameters or persistence.
     */
    public function getActiveAnomalies(Universe $universe): array
    {
        $params = $universe->getParameters();
        if (isset($params['active_anomalies']) && is_array($params['active_anomalies'])) {
            return $params['active_anomalies'];
        }
        return [];
    }

    public function triggerPotentialAnomaly(Universe $universe): ?string
    {
        $chance = mt_rand(1, 100);
        
        if ($chance <= 2) { // 2% chance per tick
            $types = [self::TYPE_VOID_STORM, self::TYPE_GOLDEN_AGE, self::TYPE_GREAT_FILTER];
            return $types[array_rand($types)];
        }

        return null;
    }

    public function applyAnomaly(Universe $universe, string $type): Universe
    {
        $state = $universe->getState();
        $params = $universe->getParameters();
        
        $newVector = null;

        switch ($type) {
            case self::TYPE_VOID_STORM:
                // Chaos increases, stability drops
                $newVector = WorldStateVector::create(
                    max(0, $state->getOrder() - 0.2),
                    min(1, $state->getEntropy() + 0.3),
                    max(0, $state->getCohesion() - 0.1),
                    $state->getLegitimacy(),
                    $state->getInnovation(),
                    $state->getMilitary(),
                    $state->getInequality(),
                    min(1, $state->getTrauma() + 0.2),
                    $state->getEliteCohesion(),
                    $state->getResourceStock()
                );
                break;

            case self::TYPE_GOLDEN_AGE:
                // Prosperity and Innovation
                $newVector = WorldStateVector::create(
                    $state->getOrder(),
                    max(0, $state->getEntropy() - 0.1),
                    min(1, $state->getCohesion() + 0.2),
                    min(1, $state->getLegitimacy() + 0.2),
                    min(1, $state->getInnovation() + 0.3),
                    $state->getMilitary(),
                    max(0, $state->getInequality() - 0.1),
                    max(0, $state->getTrauma() - 0.1),
                    min(1, $state->getEliteCohesion() + 0.1),
                    min(1, $state->getResourceStock() + 0.2)
                );
                break;

            case self::TYPE_GREAT_FILTER:
                // Threat that requires military or cohesion
                if ($state->getMilitary() < 0.5 && $state->getCohesion() < 0.5) {
                    // Massive damage if failed
                    $newVector = WorldStateVector::create(
                        $state->getOrder(),
                        min(1, $state->getEntropy() + 0.4),
                        max(0, $state->getCohesion() - 0.3),
                        max(0, $state->getLegitimacy() - 0.3),
                        $state->getInnovation(),
                        $state->getMilitary(),
                        min(1, $state->getInequality() + 0.2),
                        min(1, $state->getTrauma() + 0.4),
                        max(0, $state->getEliteCohesion() - 0.2),
                        max(0, $state->getResourceStock() - 0.3)
                    );
                } else {
                    // Survive but with cost
                    $newVector = WorldStateVector::create(
                        $state->getOrder(),
                        $state->getEntropy(),
                        $state->getCohesion(),
                        min(1, $state->getLegitimacy() + 0.1),
                        $state->getInnovation(),
                        max(0, $state->getMilitary() - 0.1),
                        $state->getInequality(),
                        $state->getTrauma(),
                        $state->getEliteCohesion(),
                        max(0, $state->getResourceStock() - 0.1)
                    );
                }
                break;
        }

        // Add to historical record
        $params['milestones'][] = [
            'age' => $universe->getAge(),
            'event' => $type,
            'description' => $this->getEventDescription($type)
        ];

        return new Universe($newVector ?? $state, $params, $universe->getId(), $universe->getAge(), $universe->getCoords());
    }

    private function getEventDescription(string $type): string
    {
        return match ($type) {
            self::TYPE_VOID_STORM => "A localized collapse of the laws of physics, leading to massive entropy leakage.",
            self::TYPE_GOLDEN_AGE => "A period of unparalleled discovery and social harmony.",
            self::TYPE_GREAT_FILTER => "A existential threat emerged, testing the universe's resolve and strength.",
            default => "An unknown cosmic anomaly occurred."
        };
    }
}
