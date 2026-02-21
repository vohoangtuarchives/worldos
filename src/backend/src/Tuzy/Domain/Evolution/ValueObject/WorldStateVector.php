<?php

namespace Tuzy\Domain\Evolution\ValueObject;

use Tuzy\Domain\Evolution\Mathematics\Vector;

class WorldStateVector extends Vector
{
    public const DIMENSION_ORDER = 'order';
    public const DIMENSION_ENTROPY = 'entropy';
    public const DIMENSION_COHESION = 'cohesion';
    public const DIMENSION_LEGITIMACY = 'legitimacy';
    public const DIMENSION_INNOVATION = 'innovation';
    public const DIMENSION_MILITARY = 'military';
    // public const DIMENSION_RSC = 'resource'; // Future use

    // Phase 7 New Dimensions
    public const DIMENSION_INEQUALITY = 'inequality';
    public const DIMENSION_TRAUMA = 'trauma';
    public const DIMENSION_ELITE_COHESION = 'elite_cohesion';
    public const DIMENSION_RESOURCE_STOCK = 'resource_stock';

    public static function create(
        float $order,
        float $entropy,
        float $cohesion,
        float $legitimacy,
        float $innovation,
        float $military,
        float $inequality = 0.0,
        float $trauma = 0.0,
        float $eliteCohesion = 0.5,
        float $resourceStock = 0.5
    ): self {
        return new self([
            self::DIMENSION_ORDER => $order,
            self::DIMENSION_ENTROPY => $entropy,
            self::DIMENSION_COHESION => $cohesion,
            self::DIMENSION_LEGITIMACY => $legitimacy,
            self::DIMENSION_INNOVATION => $innovation,
            self::DIMENSION_MILITARY => $military,
            self::DIMENSION_INEQUALITY => $inequality,
            self::DIMENSION_TRAUMA => $trauma,
            self::DIMENSION_ELITE_COHESION => $eliteCohesion,
            self::DIMENSION_RESOURCE_STOCK => $resourceStock,
        ]);
    }

    /** Build from array (e.g. state_jsonb from snapshot or after replay). */
    public static function fromArray(array $arr): self
    {
        $d = self::dimensions();
        $components = [];
        foreach ($d as $dim) {
            $components[$dim] = (float) ($arr[$dim] ?? 0.0);
        }
        return new self($components);
    }

    public function getOrder(): float { return $this->get(self::DIMENSION_ORDER); }
    public function getEntropy(): float { return $this->get(self::DIMENSION_ENTROPY); }
    public function getCohesion(): float { return $this->get(self::DIMENSION_COHESION); }
    public function getLegitimacy(): float { return $this->get(self::DIMENSION_LEGITIMACY); }
    public function getInnovation(): float { return $this->get(self::DIMENSION_INNOVATION); }
    public function getMilitary(): float { return $this->get(self::DIMENSION_MILITARY); }
    
    public function getInequality(): float { return $this->get(self::DIMENSION_INEQUALITY); }
    public function getTrauma(): float { return $this->get(self::DIMENSION_TRAUMA); }
    public function getEliteCohesion(): float { return $this->get(self::DIMENSION_ELITE_COHESION); }
    public function getResourceStock(): float { return $this->get(self::DIMENSION_RESOURCE_STOCK); }

    /** All dimensions in canonical order for gradient/divergence/curvature. */
    public static function dimensions(): array
    {
        return [
            self::DIMENSION_ORDER,
            self::DIMENSION_ENTROPY,
            self::DIMENSION_COHESION,
            self::DIMENSION_LEGITIMACY,
            self::DIMENSION_INNOVATION,
            self::DIMENSION_MILITARY,
            self::DIMENSION_INEQUALITY,
            self::DIMENSION_TRAUMA,
            self::DIMENSION_ELITE_COHESION,
            self::DIMENSION_RESOURCE_STOCK,
        ];
    }

    /**
     * Gradient: difference vector (this - prev) per dimension.
     * Represents rate of change / velocity in phase space.
     */
    public function gradient(WorldStateVector $prev): Vector
    {
        return $this->subtract($prev);
    }

    /**
     * Divergence: scalar measure of "spread" or instability in state.
     * Defined as variance of components (high = more spread/instability).
     */
    public function divergence(): float
    {
        $vals = array_values($this->getAll());
        $n = count($vals);
        if ($n === 0) {
            return 0.0;
        }
        $mean = array_sum($vals) / $n;
        $variance = 0.0;
        foreach ($vals as $v) {
            $variance += ($v - $mean) ** 2;
        }
        return $variance / $n;
    }

    /**
     * Curvature: magnitude of gradient (rate of change) from prev to this.
     * High curvature = trajectory changing fast = instability stress.
     */
    public function curvature(WorldStateVector $prev): float
    {
        return $this->gradient($prev)->magnitude();
    }
}


