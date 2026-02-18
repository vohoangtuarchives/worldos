<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Models\CosmicFaction;
use App\Models\UniverseModel;
use Illuminate\Support\Facades\DB;

/**
 * When a universe enters structural collapse (StructuralMutationEngine path),
 * compute which factions survive and update lineage (Phase 4.1).
 */
class FactionSurvivalService
{

    /**
     * Call after a tick that resulted in collapse (BasePhysicsEngine getLastAssessment()['should_collapse']).
     * Updates faction status and optionally creates survivor faction with lineage.
     */
    public function onUniverseCollapse(string $universeId, int $tick): void
    {
        $model = UniverseModel::find($universeId);
        if ($model === null || $model->cosmic_faction_id === null) {
            return;
        }

        $faction = CosmicFaction::find($model->cosmic_faction_id);
        if ($faction === null) {
            return;
        }

        $survivalScore = $this->computeSurvivalScore($faction);
        $survives = $survivalScore > 0.3; // Simple threshold; can use pressure later

        if ($survives) {
            $survivor = new CosmicFaction();
            $survivor->name = $faction->name . ' (Reborn)';
            $survivor->ideology = $faction->ideology;
            $survivor->color = $faction->color;
            $survivor->stats = $faction->stats;
            $survivor->status = CosmicFaction::STATUS_ACTIVE;
            $survivor->parent_faction_id = $faction->id;
            $survivor->cycle_origin = $tick;
            $survivor->cycles_survived = ($faction->cycles_survived ?? 0) + 1;
            $survivor->ideology_adaptability = $faction->ideology_adaptability ?? 0.5;
            $survivor->resource_control = $faction->resource_control ?? 0.5;
            $survivor->network_resilience = $faction->network_resilience ?? 0.5;
            $survivor->save();

            $model->cosmic_faction_id = $survivor->id;
            $model->save();
        } else {
            $model->cosmic_faction_id = null;
            $model->save();
        }

        $faction->status = $survives ? CosmicFaction::STATUS_DISSOLVED : CosmicFaction::STATUS_ERADICATED;
        $faction->save();

        $this->recordCycle($universeId, $tick);
    }

    private function recordCycle(string $universeId, int $endTick): void
    {
        $nextCycle = (int) DB::table('civilization_cycles')
            ->where('universe_id', $universeId)
            ->count() + 1;

        DB::table('civilization_cycles')->insert([
            'universe_id' => $universeId,
            'cycle_number' => $nextCycle,
            'start_tick' => 0,
            'end_tick' => $endTick,
            'collapse_reason' => 'STRUCTURAL_COLLAPSE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function computeSurvivalScore(CosmicFaction $faction): float
    {
        $a = $faction->ideology_adaptability ?? 0.5;
        $r = $faction->resource_control ?? 0.5;
        $n = $faction->network_resilience ?? 0.5;
        return ($a + $r + $n) / 3.0;
    }
}
