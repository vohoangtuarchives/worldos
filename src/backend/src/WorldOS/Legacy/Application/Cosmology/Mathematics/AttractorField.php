<?php

namespace WorldOS\Legacy\Application\Cosmology\Mathematics;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Domain\Cosmology\ValueObjects\Attractor;

/**
 * Centroid-based attractor field. Uses DominanceCalculator for influence-based classification.
 * Backward compatible: classify() returns basin name of dominant attractor.
 */
class AttractorField
{
    public const BASIN_STABILITY = 'STABILITY';
    public const BASIN_FRAGMENTATION = 'FRAGMENTATION';
    public const BASIN_WAR = 'WAR';
    public const BASIN_STAGNATION = 'STAGNATION';
    public const BASIN_COLLAPSE = 'COLLAPSE';
    public const BASIN_RENAISSANCE = 'RENAISSANCE';

    private ?array $attractors = null;
    private DominanceCalculator $dominanceCalculator;

    public function __construct(?DominanceCalculator $dominanceCalculator = null)
    {
        $this->dominanceCalculator = $dominanceCalculator ?? new DominanceCalculator();
    }

    /**
     * Default attractors with centroids in WorldStateVector dimension order.
     * order, entropy, cohesion, legitimacy, innovation, military, inequality, trauma, elite_cohesion, resource_stock
     *
     * @return list<Attractor>
     */
    public function getAttractors(): array
    {
        if ($this->attractors !== null) {
            return $this->attractors;
        }
        $d = WorldStateVector::dimensions();
        $defaults = [
            self::BASIN_STABILITY    => [0.75, 0.2, 0.75, 0.7, 0.4, 0.2, 0.2, 0.1, 0.6, 0.6],
            self::BASIN_COLLAPSE     => [0.2, 0.9, 0.1, 0.1, 0.2, 0.5, 0.7, 0.8, 0.2, 0.2],
            self::BASIN_WAR          => [0.4, 0.5, 0.35, 0.3, 0.3, 0.85, 0.5, 0.6, 0.5, 0.4],
            self::BASIN_FRAGMENTATION => [0.35, 0.55, 0.35, 0.35, 0.4, 0.4, 0.6, 0.4, 0.4, 0.4],
            self::BASIN_STAGNATION   => [0.85, 0.35, 0.6, 0.6, 0.1, 0.3, 0.4, 0.3, 0.7, 0.5],
            self::BASIN_RENAISSANCE  => [0.5, 0.3, 0.65, 0.6, 0.85, 0.2, 0.3, 0.2, 0.5, 0.7],
        ];
        $this->attractors = [];
        foreach ($defaults as $name => $vals) {
            $centroid = array_combine($d, $vals);
            $this->attractors[] = new Attractor($name, $centroid, $centroid);
        }
        return $this->attractors;
    }

    /**
     * Inject custom attractors (e.g. from DB after drift).
     *
     * @param list<Attractor> $attractors
     */
    public function setAttractors(array $attractors): void
    {
        $this->attractors = $attractors;
    }

    /**
     * Classify state into single basin (dominant attractor). Backward compatible.
     */
    public function classify(WorldStateVector $state): string
    {
        $dominant = $this->dominanceCalculator->dominantAttractor($state, $this->getAttractors());
        return $dominant ?? self::BASIN_FRAGMENTATION;
    }

    /**
     * Full influence vector for meta-history (attractor name => influence 0..1, sum = 1).
     *
     * @return array<string, float>
     */
    public function influences(WorldStateVector $state): array
    {
        return $this->dominanceCalculator->influences($state, $this->getAttractors());
    }
}
