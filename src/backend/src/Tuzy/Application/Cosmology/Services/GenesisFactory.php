<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Domain\Cosmology\Contracts\StructuralAnchorInterface;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Cosmology\ValueObject\ConstraintProfile;

/** Creates initial WorldStateVector from Anchor + ConstraintProfile (Genesis / Forge). */
final class GenesisFactory
{
    public function createState(
        StructuralAnchorInterface $anchor,
        ConstraintProfile $profile
    ): WorldStateVector {
        $key = $anchor->getKey();
        $pg = $profile->toArray();

        $order = 0.6;
        $entropy = 0.2;
        $cohesion = 0.6;
        $legitimacy = 0.6;
        $innovation = 0.4;
        $military = 0.2;
        $inequality = 0.3;
        $trauma = 0.0;
        $eliteCohesion = 0.5;
        $resourceStock = 0.5;

        if ($key === 'academic_system') {
            $cohesion = 0.7;
            $legitimacy = 0.7;
            $innovation = 0.5;
            $resourceStock = 0.6;
        } elseif ($key === 'faction_system') {
            $military = 0.4;
            $inequality = 0.5;
            $eliteCohesion = 0.6;
            $cohesion = 0.5;
        } elseif ($key === 'commercial_system') {
            $resourceStock = 0.6;
            $inequality = 0.4;
            $order = 0.7;
        }

        if (($pg['power_gradient'] ?? '') === 'steep') {
            $inequality = min(1.0, $inequality + 0.2);
            $eliteCohesion = min(1.0, $eliteCohesion + 0.1);
        }
        if (($pg['resource_density'] ?? '') === 'scarce') {
            $resourceStock = max(0.1, $resourceStock - 0.2);
        }
        if (($pg['conflict_intensity'] ?? '') === 'high') {
            $military = min(1.0, $military + 0.15);
            $entropy = min(1.0, $entropy + 0.1);
        }

        return WorldStateVector::create(
            $order, $entropy, $cohesion, $legitimacy, $innovation,
            $military, $inequality, $trauma, $eliteCohesion, $resourceStock
        );
    }
}
