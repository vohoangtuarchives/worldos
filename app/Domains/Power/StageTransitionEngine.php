<?php

namespace App\Domains\Power;

use App\Domains\Power\Enums\PowerStage;
use Illuminate\Support\Facades\DB;

class StageTransitionEngine
{
    private array $thresholds = [
        PowerStage::STAGE_1_MORTAL_MARTIAL->value => 0.4,
        PowerStage::STAGE_2_ENHANCED_MARTIAL->value => 0.7,
        PowerStage::STAGE_3_LOW_IMMORTAL->value => 1.0,
        PowerStage::STAGE_4_HIGH_IMMORTAL->value => 1.5,
        PowerStage::STAGE_5_MYTHIC->value => 2.0,
    ];

    public function evaluate(int $worldId): ?PowerStage
    {
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        if (!$state) return null;

        $currentStage = PowerStage::from($state->current_stage);
        $pressure = (float) $state->accumulated_pressure;
        $phase = $state->transition_phase;

        if ($phase === 'stable') {
            foreach ($this->thresholds as $stageValue => $threshold) {
                $stage = PowerStage::from($stageValue);
                if ($stage->level() > $currentStage->level() && $pressure >= $threshold) {
                    $this->startPreTransition($worldId, $stage);
                    return null; // Not transitioned yet, just started pre-phase
                }
            }
        }

        return null;
    }

    public function startPreTransition(int $worldId, PowerStage $targetStage): void
    {
        DB::table('world_power_stages')->where('world_id', $worldId)->update([
            'transition_phase' => 'pre',
            'target_stage' => $targetStage->value,
            'updated_at' => now(),
        ]);

        // TODO: Dispatch Event: PotentialEvolutionDetected
    }

    public function triggerMoment(int $worldId): void
    {
        DB::table('world_power_stages')->where('world_id', $worldId)->update([
            'transition_phase' => 'moment',
            'updated_at' => now(),
        ]);

        // TODO: Dispatch Event: RealityBreakOccurring
    }

    public function startStabilization(int $worldId): void
    {
        DB::table('world_power_stages')->where('world_id', $worldId)->update([
            'transition_phase' => 'post',
            'updated_at' => now(),
        ]);

        // TODO: Dispatch Event: RealityStabilizing
    }

    public function completeTransition(int $worldId): void
    {
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        
        if (!$state || !$state->target_stage) return;

        DB::table('world_power_stages')->where('world_id', $worldId)->update([
            'current_stage' => $state->target_stage,
            'transition_phase' => 'stable',
            'target_stage' => null,
            'updated_at' => now(),
        ]);

        // TODO: Dispatch Event: WorldEvolutionComplete
    }
}
