<?php

namespace App\Domains\Faction\Services;

use App\Models\World;
use App\Models\Faction;
use App\Domains\Faction\Enums\FactionIntentType;

class ConflictResolver
{
    public function __construct(
        private \App\Domains\Faction\Policies\CivilWarPolicy $civilWarPolicy,
        private \App\Domains\World\Services\WorldEventLedger $ledger
    ) {}

    /**
     * Resolve all faction intents for a tick.
     */
    public function resolve(World $world, array $factionIntents): void
    {
        // 1. Sort intents by priority if needed (e.g. ATTACK resolves before RECOVER)
        // For simplicity, we process them in groups
        
        $groups = [
            'attack' => [],
            'alliance' => [],
            'expand' => [],
            'invoke_myth' => [],
            'suppress_scar' => [],
            'recover' => [],
            'split' => [],
        ];

        foreach ($factionIntents as $factionId => $intent) {
            $groups[$intent->value][] = $factionId;
        }

        // 2. Resolve Group by Group
        $this->resolveAttacks($world, $groups['attack']);
        $this->resolveAlliances($world, $groups['alliance']);
        $this->resolveExpansions($world, $groups['expand']);
        $this->resolveMyths($world, $groups['invoke_myth']);
        $this->resolveScars($world, $groups['suppress_scar']);
        $this->resolveRecoveries($world, $groups['recover']);
        $this->resolveSplits($world, $groups['split']);
    }

    private function resolveAttacks(World $world, array $factionIds): void
    {
        // ... (rest of methods)
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            // Simple victory/defeat logic based on military power (if we had it)
            // For now, 60% chance of success if aggression is high
            $personality = $faction->getPersonality();
            $chance = 0.5 + ($personality->aggression * 0.2);
            $success = (mt_rand(0, 100) / 100) < $chance;
            
            $reward = $success ? 1.0 : -1.2; // Defeat hurts more
            
            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => [
                    'result' => $success ? 'victory' : 'defeat',
                    'aggression_bonus' => $personality->aggression > 0.7
                ]
            ]);
            $faction->save();

            // Record major victories in the Ledger
            if ($success && $personality->aggression > 0.8) {
                $this->ledger->record(
                    $world,
                    'military_victory',
                    "Phe {$faction->name} đã giành chiến thắng quyết định, khẳng định bá quyền.",
                    0.25, // Significant magnitude
                    0.8   // High permanence
                );
            }
        }
    }

    private function resolveAlliances(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            $success = mt_rand(0, 100) > 40;
            $reward = $success ? 0.8 : -0.2;

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => $success ? 'alliance_formed' : 'negotiation_failed']
            ]);
            $faction->save();

            if ($success) {
                $this->ledger->record(
                    $world,
                    'diplomatic_treaty',
                    "Một liên minh mới đã được thành lập bởi {$faction->name}, làm thay đổi cục diện chính trị.",
                    0.15,
                    0.6
                );
            }
        }
    }

    private function resolveExpansions(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            $success = mt_rand(0, 100) > 30;
            $reward = $success ? 0.5 : -0.1;

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => $success ? 'territory_gained' : 'expansion_stalled']
            ]);
            $faction->save();
        }
    }

    private function resolveMyths(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            // High faith helps
            $personality = $faction->getPersonality();
            $chance = 0.4 + ($personality->faith * 0.3);
            $success = (mt_rand(0, 100) / 100) < $chance;

            $reward = $success ? 1.5 : -0.8;

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => $success ? 'myth_invoked' : 'divine_silence']
            ]);
            $faction->save();
        }
    }

    private function resolveScars(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            $success = mt_rand(0, 100) > 50;
            $reward = $success ? 1.0 : 0.0;

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => $success ? 'scar_suppressed' : 'trauma_remains']
            ]);
            $faction->save();
        }
    }

    private function resolveRecoveries(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            $reward = 0.4; // Usually safe

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => 'resources_recovered']
            ]);
            $faction->save();
        }
    }

    private function resolveSplits(World $world, array $factionIds): void
    {
        foreach ($factionIds as $id) {
            $faction = Faction::find($id);
            
            if ($this->civilWarPolicy->shouldSplit($faction)) {
                $rebel = $this->civilWarPolicy->executeSplit($faction);
                $reward = -2.0; // Civil war is disastrous
                $log = 'civil_war_started';
            } else {
                $reward = -0.1; // Tension remains but no split
                $log = 'tension_simmering';
            }

            $faction->attributes = array_merge($faction->attributes ?? [], [
                'tick_reward' => $reward,
                'tick_reason' => ['result' => $log]
            ]);
            $faction->save();

            if ($log === 'civil_war_started') {
                $this->ledger->record(
                    $world,
                    'civil_war',
                    "Nội chiến bùng nổ trong lòng {$faction->name}, di sản cũ bị xé nát.",
                    0.4,
                    0.9
                );
            }
        }
    }
