<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;
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
    public function generateEvents(CivilizationState $state, string $phase, string $seed): array
    {
        $events = [];
        $rng = hexdec(substr(md5($seed), 0, 8)) / 0xffffffff;
        
        // Number of events (0-3) - Biased by chaos
        $chaos = (1.0 - $state->stability) * 0.5 + $state->internalEntropy * 0.5;
        $count = (int) round($rng * 2 + $chaos);
        $count = max(0, min(3, $count));

        for ($i = 0; $i < $count; $i++) {
            $eventSeed = $seed . "_event_{$i}";
            $type = $this->determineEventType($phase, $state, $eventSeed);
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
     * Determine event type based on phase probability bias.
     */
    private function determineEventType(string $phase, CivilizationState $state, string $seed): string
    {
        $rng = hexdec(substr(md5($seed), 0, 4)) / 0xffff;
        
        $weights = match($phase) {
            PhaseEngine::PHASE_WAR => [
                self::TYPE_BATTLE => 0.6,
                self::TYPE_FAMINE => 0.2,
                self::TYPE_HERO_BIRTH => 0.1,
                self::TYPE_REBELLION => 0.1,
            ],
            PhaseEngine::PHASE_GOLDEN_AGE => [
                self::TYPE_CULTURAL_BLOSSOM => 0.5,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.2,
                self::TYPE_HERO_BIRTH => 0.2,
                self::TYPE_REFORM => 0.1,
            ],
            PhaseEngine::PHASE_FRAGMENTATION => [
                self::TYPE_REBELLION => 0.4,
                self::TYPE_BATTLE => 0.3,
                self::TYPE_FAMINE => 0.2,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.1,
            ],
            default => [
                self::TYPE_BATTLE => 0.1,
                self::TYPE_REFORM => 0.2,
                self::TYPE_RELIGIOUS_MOVEMENT => 0.2,
                self::TYPE_CULTURAL_BLOSSOM => 0.2,
                self::TYPE_HERO_BIRTH => 0.3,
            ]
        };

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
    public function applyImpacts(CivilizationState $state, array $events): array
    {
        $mods = [
            'stability' => 0.0,
            'prosperity' => 0.0,
            'cultural_energy' => 0.0,
            'military_pressure' => 0.0,
            'internal_entropy' => 0.0,
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
                case self::TYPE_REFORM:
                    if ($event['success']) {
                        $mods['stability'] += $impact * 0.2;
                        $mods['internal_entropy'] -= $impact * 0.1;
                    } else {
                        $mods['stability'] -= $impact * 0.1;
                        $mods['internal_entropy'] += $impact * 0.2;
                    }
                    break;
            }
        }

        return $mods;
    }
}
