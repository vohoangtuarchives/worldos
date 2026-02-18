<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Adapter;

use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmology\Entities\WorldStateVector;

/**
 * SnapshotToVectorAdapter - Converts scalar WorldSnapshot (DCE) to WorldStateVector.
 * Used at load boundary only.
 */
final class SnapshotToVectorAdapter
{
    public function toVector(WorldSnapshot $snapshot): WorldStateVector
    {
        $cosmic = $snapshot->cosmic;
        $civ = $snapshot->civilization;

        $order = 1.0 - $cosmic->entropy;
        $entropy = $cosmic->entropy;
        $cohesion = $civ->spiritualCohesion;
        $legitimacy = $civ->stability;
        $innovation = min(1.0, ($civ->culturalEnergy + $civ->technologicalLevel / 2.0) / 1.5);
        $military = $civ->militaryPressure;
        $inequality = $civ->internalEntropy;
        $trauma = min(1.0, $cosmic->strain * 2.0);
        $eliteCohesion = 0.5 * $civ->spiritualCohesion + 0.5 * max(0, 1.0 - $civ->internalEntropy);
        $resourceStock = $civ->prosperity;

        return WorldStateVector::create(
            order: max(0, min(1, $order)),
            entropy: max(0, min(1, $entropy)),
            cohesion: max(0, min(1, $cohesion)),
            legitimacy: max(0, min(1, $legitimacy)),
            innovation: max(0, min(1, $innovation)),
            military: max(0, min(1, $military)),
            inequality: max(0, min(1, $inequality)),
            trauma: max(0, min(1, $trauma)),
            eliteCohesion: max(0, min(1, $eliteCohesion)),
            resourceStock: max(0, min(1, $resourceStock))
        );
    }
}
