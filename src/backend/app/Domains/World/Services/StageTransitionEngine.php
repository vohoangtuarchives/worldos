<?php

namespace App\Domains\World\Services;

use App\Models\World;
use App\Domains\Power\PowerStageRegistry;
use WorldOS\Legacy\Domain\Power\ValueObject\PowerStage;
use App\Models\WorldPowerProfile;

class StageTransitionEngine
{
    public function __construct(
        private WorldEventLedger $ledger,
        private PowerStageRegistry $registry
    ) {}

    /**
     * Evaluate and trigger stage transition if pressure threshold met
     */
    public function evaluateTransition(World $world): bool
    {
        $profile = $world->powerProfile;
        $currentStageKey = $profile?->progression_state['current_stage'] ?? $world->config['current_stage'] ?? 'mundane';
        $stageInfo = $this->registry->getStageAndConstraint($currentStageKey);
        
        if (empty($stageInfo)) {
            return false;
        }

        $pressure = $this->ledger->calculateGlobalPressure($world);
        $threshold = $this->getThresholdForStage($currentStageKey);

        if ($pressure >= $threshold) {
            return $this->transitionToNextStage($world, $profile, $currentStageKey, $pressure);
        }

        return false;
    }

    /**
     * Execute the transition to the next logical stage
     */
    private function transitionToNextStage(World $world, ?WorldPowerProfile $profile, string $currentStageKey, float $pressure): bool
    {
        $nextStageKey = $this->getNextStageKey($currentStageKey);
        
        if (!$nextStageKey || $nextStageKey === $currentStageKey) {
            return false;
        }

        // Record the "Moment of Transition" in the Ledger
        $this->ledger->record(
            $world,
            'stage_transition',
            "Thế giới đã phá vỡ rào cản cũ, bước vào kỷ nguyên {$nextStageKey}.",
            1.0, // Maximum magnitude for transition
            1.0  // Permanent change
        );

        // Update World Config
        $config = $world->config ?? [];
        $config['current_stage'] = $nextStageKey;
        $world->config = $config;
        $world->save();

        if ($profile) {
            $state = $profile->progression_state;
            $state['current_stage'] = $nextStageKey;
            $state['pressure'] = $pressure;
            $state['stage_history'][] = [
                'from' => $currentStageKey,
                'to' => $nextStageKey,
                'pressure' => $pressure,
                'timestamp' => now()->toIso8601String(),
            ];
            $profile->progression_state = $state;
            $profile->save();
        }

        return true;
    }

    /**
     * Thresholds based on spec: 0.7 for major shifts, etc.
     */
    private function getThresholdForStage(string $stageKey): float
    {
        return match ($stageKey) {
            'mundane' => 0.7,
            'mortal_martial' => 2.0,
            'enhanced_martial' => 5.0,
            'low_immortal' => 10.0,
            'high_immortal' => 20.0,
            default => INF,
        };
    }

    private function getNextStageKey(string $stageKey): ?string
    {
        $stages = [
            'mundane',
            'mortal_martial',
            'enhanced_martial',
            'low_immortal',
            'high_immortal',
            'mythic'
        ];

        $index = array_search($stageKey, $stages);
        
        if ($index !== false && isset($stages[$index + 1])) {
            return $stages[$index + 1];
        }

        return null;
    }
}
