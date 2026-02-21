<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\Enum\CivilizationPhase;
use Tuzy\Domain\Evolution\Enum\CivilizationLifecycleState;
use Illuminate\Support\Str;

/**
 * EventEngine
 * 
 * Generates dynamic events based on civilization phase and state metrics.
 * Implements feedback loops by calculating state impacts.
 */
class EventEngine
{
    public const TYPE_BATTLE = 'battle';
    public const TYPE_FAMINE = 'famine';
    public const TYPE_RELIGIOUS_MOVEMENT = 'religious_movement';
    public const TYPE_CULTURAL_BLOSSOM = 'cultural_blossom';
    public const TYPE_HERO_BIRTH = 'hero_birth';
    public const TYPE_REBELLION = 'rebellion';
    public const TYPE_REFORM = 'reform';

    /**
     * Generate 0-3 events based on current state and phase.
     */
    public function generateEvents(CivilizationSnapshot $state, CivilizationPhase $phase, string $seed, array $pressures = [], float $totalFactionPower = 1.0): array
    {
        // 1. Lifecycle Guard: Extinct civilizations generate no events
        if ($state->lifecycleState === CivilizationLifecycleState::EXTINCT) {
            return [];
        }

        $events = [];
        $rng = hexdec(substr(md5($seed), 0, 8)) / 0xffffffff;
        
        // Number of events (0-3) - Biased by chaos and total pressure
        $totalPressure = array_sum($pressures) / count($pressures ?: [1]);
        $chaos = (1.0 - $state->stability) * 0.4 + $state->internalEntropy * 0.3 + $totalPressure * 0.3;
        $count = (int) round($rng * 2 + ($chaos * 2));
        $count = max(0, min(3, $count));

        for ($i = 0; $i < $count; $i++) {
            $eventSeed = $seed . "_event_{$i}";
            $type = $this->determineEventType($phase, $state, $eventSeed, $pressures, $totalFactionPower);
            $intensity = (hexdec(substr(md5($eventSeed), 0, 4)) / 0xffff) * 0.5 + ($chaos * 0.5);
            $success = (hexdec(substr(md5($eventSeed), 4, 4)) / 0xffff) > 0.4;

            $events[] = [
                'type' => $type,
                'intensity' => round($intensity, 4),
                'success' => $success,
                'scale' => rand(1, 5),
                'id' => Str::uuid()->toString(),
            ];
        }

        return $events;
    }

    /**
     * Determine event type based on phase probability bias and pressures.
     */
    private function determineEventType(CivilizationPhase $phase, CivilizationSnapshot $state, string $seed, array $pressures = [], float $totalFactionPower = 1.0): string
    {
        $rng = hexdec(substr(md5($seed), 0, 4)) / 0xffff;
        
        $narrativeEngine = new \Tuzy\Domain\Evolution\Service\NarrativeEngine();
        $phaseWeights = $narrativeEngine->computePhaseWeights($state->narrativeTension);
        
        if ($state->historyPhase === \Tuzy\Domain\Evolution\Enum\CivilizationPhase::ILLUMINATION) {
             // Override narrative softmax completely for the golden mythic era
             $phaseWeights = ['illumination' => 1.0];
        }

        // Define bias for each narrative phase
        $biases = [
            'growth' => [
                self::TYPE_CULTURAL_BLOSSOM => 0.5,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.2,
                self::TYPE_REFORM => 0.2,
                self::TYPE_HERO_BIRTH => 0.1,
            ],
            'stress' => [
                self::TYPE_REBELLION => 0.3,
                self::TYPE_BATTLE => 0.3,
                self::TYPE_HERO_BIRTH => 0.2, // Heroes are more likely under stress
                self::TYPE_FAMINE => 0.2,
            ],
            'decline' => [
                self::TYPE_REBELLION => 0.4,
                self::TYPE_BATTLE => 0.4,
                self::TYPE_FAMINE => 0.1,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.1,
            ],
            'collapse' => [
                self::TYPE_BATTLE => 0.5,
                self::TYPE_FAMINE => 0.3,
                self::TYPE_REBELLION => 0.2,
                // Hero is very rare in total collapse
            ],
            'illumination' => [
                self::TYPE_CULTURAL_BLOSSOM => 0.6,
                self::TYPE_REFORM => 0.2,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.15,
                self::TYPE_HERO_BIRTH => 0.05,
            ]
        ];

        // Combine probabilities
        $weights = [];
        foreach ($phaseWeights as $pK => $prob) {
            foreach ($biases[$pK] as $type => $baseRate) {
                if (!isset($weights[$type])) $weights[$type] = 0.0;
                $weights[$type] += $prob * $baseRate;
            }
        }

        // BÓC TÁCH ÁP LỰC: Nếu áp lực xã hội cao, tăng trọng số Rebellon/Battle
        if (($pressures['social_instability'] ?? 0) > 0.6) {
            $weights[self::TYPE_REBELLION] = ($weights[self::TYPE_REBELLION] ?? 0) + 0.3;
            $weights[self::TYPE_BATTLE] = ($weights[self::TYPE_BATTLE] ?? 0) + 0.2;
        }
        
        if (($pressures['metaphysical_tension'] ?? 0) > 0.7) {
            $weights[self::TYPE_RELIGIOUS_MOVEMENT] = ($weights[self::TYPE_RELIGIOUS_MOVEMENT] ?? 0) + 0.4;
        }

        // 2. Eligibility Guard: Filter out types that aren't possible given the physical state
        if ($totalFactionPower < 0.1) {
            unset($weights[self::TYPE_BATTLE]);
        }
        
        if ($state->prosperity < 0.05) {
            unset($weights[self::TYPE_FAMINE]); // Hard to have a famine if there's no prosperity to lose or population to starve
        }

        if (empty($weights)) {
            return self::TYPE_CULTURAL_BLOSSOM; // Fallback
        }

        // Normalize weights
        $total = array_sum($weights);
        foreach ($weights as $k => $v) $weights[$k] = $v / $total;


        $cumulative = 0;
        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rng <= $cumulative) {
                return $type;
            }
        }

        return self::TYPE_HERO_BIRTH;
    }

    /**
     * Apply event impacts to the civilization state (Feedback Loop).
     */
    public function applyImpacts(CivilizationSnapshot $state, array $events): array
    {
        $mods = [
            'stability' => 0.0,
            'prosperity' => 0.0,
            'cultural_energy' => 0.0,
            'military_pressure' => 0.0,
            'internal_entropy' => 0.0,
            'trauma' => [
                'war' => 0.0,
                'metaphysical' => 0.0,
                'social' => 0.0,
            ]
        ];

        foreach ($events as $event) {
            $impact = $event['intensity'] * ($event['scale'] / 5);
            
            switch ($event['type']) {
                case self::TYPE_BATTLE:
                    if ($event['success']) {
                        $mods['stability'] += $impact * 0.1;
                        $mods['military_pressure'] -= $impact * 0.2;
                    } else {
                        $mods['stability'] -= $impact * 0.2;
                        $mods['internal_entropy'] += $impact * 0.1;
                        $mods['trauma']['war'] += $impact * 0.3;
                    }
                    break;
                case self::TYPE_FAMINE:
                    $mods['prosperity'] -= $impact * 0.2;
                    $mods['stability'] -= $impact * 0.1;
                    $mods['internal_entropy'] += $impact * 0.1;
                    break;
                case self::TYPE_REBELLION:
                    $mods['stability'] -= $impact * 0.3;
                    $mods['internal_entropy'] += $impact * 0.2;
                    $mods['military_pressure'] += $impact * 0.1;
                    break;
                case self::TYPE_CULTURAL_BLOSSOM:
                    $mods['cultural_energy'] += $impact * 0.2;
                    $mods['prosperity'] += $impact * 0.1;
                    break;
                case 'field_shock':
                    $mods['stability'] -= $impact * 0.3;
                    $mods['internal_entropy'] += $impact * 0.2;
                    $mods['trauma']['social'] += $impact * 0.2;
                    break;
                case 'anomaly':
                    $mods['internal_entropy'] += $impact * 0.3;
                    $mods['trauma']['metaphysical'] += $impact * 0.4;
                    break;
                case self::TYPE_REFORM:
                    if ($event['success']) {
                        $mods['stability'] += $impact * 0.2;
                        $mods['internal_entropy'] -= $impact * 0.1;
                    } else {
                        $mods['stability'] -= $impact * 0.1;
                        $mods['internal_entropy'] += $impact * 0.2;
                    }
                    break;
                case 'transcendence':
                    $mods['technological_level'] += 0.2;
                    $mods['internal_entropy'] -= 0.3;
                    $mods['cultural_energy'] += 0.2;
                    $mods['long_wave_tension'] -= 0.4;
                    break;
            }
        }

        return $mods;
    }
}




