<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Anchors;

use App\Domains\Cosmology\Contracts\StructuralAnchorInterface;

/** Commercial Anchor: capital flow, trade war, merchant/broker. Resource: capital. */
final class CommercialAnchor implements StructuralAnchorInterface
{
    public function getKey(): string
    {
        return 'commercial_system';
    }

    public function generateInstitutions(): array
    {
        return [
            'exchange' => ['role' => 'capital flow', 'weight' => 0.9],
            'guild' => ['role' => 'cartel', 'weight' => 0.8],
            'court' => ['role' => 'contract enforcement', 'weight' => 0.6],
            'warehouse' => ['role' => 'logistics', 'weight' => 0.5],
        ];
    }

    public function generateConflictTopology(): array
    {
        return [
            'primary' => 'trade_war',
            'secondary' => ['monopoly', 'debt', 'embargo'],
            'actors' => ['guilds', 'merchants', 'brokers'],
        ];
    }

    public function protagonistArchetypes(): array
    {
        return ['merchant', 'broker', 'smuggler', 'guild_master'];
    }

    public function resourceFlowModel(): array
    {
        return [
            'primary_resource' => 'capital',
            'distribution' => 'market_based',
            'scarcity_driver' => 'liquidity',
        ];
    }
}
