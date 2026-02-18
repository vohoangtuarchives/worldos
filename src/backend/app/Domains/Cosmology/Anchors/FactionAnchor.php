<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Anchors;

use App\Domains\Cosmology\Contracts\StructuralAnchorInterface;

final class FactionAnchor implements StructuralAnchorInterface
{
    public function getKey(): string
    {
        return 'faction_system';
    }

    public function generateInstitutions(): array
    {
        return [
            'council' => ['role' => 'political hierarchy', 'weight' => 0.9],
            'military' => ['role' => 'enforcement', 'weight' => 0.8],
            'intelligence' => ['role' => 'information', 'weight' => 0.7],
            'tribunal' => ['role' => 'legitimacy', 'weight' => 0.6],
        ];
    }

    public function generateConflictTopology(): array
    {
        return [
            'primary' => 'coup',
            'secondary' => ['succession', 'territory', 'alliance_betrayal'],
            'actors' => ['factions_by_power', 'leaders', 'spies'],
        ];
    }

    public function protagonistArchetypes(): array
    {
        return ['leader', 'spy', 'loyalist', 'traitor'];
    }

    public function resourceFlowModel(): array
    {
        return [
            'primary_resource' => 'authority',
            'distribution' => 'power_based',
            'scarcity_driver' => 'military_control',
        ];
    }
}
