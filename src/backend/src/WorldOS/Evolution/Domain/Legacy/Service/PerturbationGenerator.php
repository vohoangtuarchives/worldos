<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldField;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use Illuminate\Support\Str;

/**
 * PerturbationGenerator
 * 
 * Generates events that affect the World Field layer.
 * These are "Agents of Change" that shift the possibility space of the world.
 */
class PerturbationGenerator
{
    public const AGENT_NATURAL = 'natural';     // Meteors, Solar flares, Geological shifts
    public const AGENT_EXOGENOUS = 'exogenous'; // Greater Beings, God Console Interventions
    public const AGENT_ENDOGENOUS = 'endogenous'; // Nuclear war, Grey Goo, Resource collapse

    /**
     * Check for potential perturbations based on world state and randomness.
     */
    public function generatePerturbations(WorldField $field, CivilizationSnapshot $state, string $seed): array
    {
        $rng = hexdec(substr(md5($seed . '_perturb'), 0, 8)) / 0xffffffff;
        $perturbations = [];

        // 1. Natural Perturbations (Baseline probability)
        if ($rng < 0.02) { // 2% chance per step for a natural event
            $perturbations[] = $this->createNaturalPerturbation($seed);
        }

        // 2. Endogenous (Based on civilization state - e.g. high entropy/low stability)
        $tensions = (1.0 - $state->stability) * 0.5 + $state->internalEntropy * 0.5;
        if ($tensions > 0.8 && $rng < 0.1) {
            $perturbations[] = $this->createEndogenousPerturbation($seed, $state);
        }

        return $perturbations;
    }

    private function createNaturalPerturbation(string $seed): array
    {
        $rng = hexdec(substr(md5($seed . '_natural'), 0, 4)) / 0xffff;
        
        if ($rng < 0.3) {
            return [
                'type' => self::AGENT_NATURAL,
                'name' => 'meteor_impact',
                'description' => 'Một thiên thạch lớn mang theo khoáng thạch lạ va chạm hành tinh.',
                'shift' => [
                    'magic' => 0.3,
                    'chaos' => 0.2,
                    'tech' => -0.1
                ]
            ];
        }

        if ($rng < 0.6) {
            return [
                'type' => self::AGENT_NATURAL,
                'name' => 'solar_storm',
                'description' => 'Bão mặt trời cường độ lớn quét qua, làm biến đổi từ trường.',
                'shift' => [
                    'psionic' => 0.15,
                    'tech' => -0.2,
                    'chaos' => 0.1
                ]
            ];
        }

        return [
            'type' => self::AGENT_NATURAL,
            'name' => 'leyline_fluctuation',
            'description' => 'Mạch linh khí trong lòng đất biến động mạnh.',
            'shift' => [
                'magic' => 0.1,
                'divine' => 0.05
            ]
        ];
    }

    private function createEndogenousPerturbation(string $seed, CivilizationSnapshot $state): array
    {
        return [
            'type' => self::AGENT_ENDOGENOUS,
            'name' => 'civilizational_collapse_feedback',
            'description' => 'Sự sụp đổ của nền văn minh để lại những vết sẹo thực tại.',
            'shift' => [
                'chaos' => 0.2,
                'magic' => 0.05,
                'psionic' => 0.05
            ]
        ];
    }

    /**
     * Create an Exogenous perturbation (e.g. from a Greater Being)
     */
    public function createExogenousPerturbation(string $name, array $shift, string $description = ''): array
    {
        return [
            'type' => self::AGENT_EXOGENOUS,
            'name' => $name,
            'description' => $description ?: "Sự can thiệp từ chiều không gian cao hơn: {$name}",
            'shift' => $shift
        ];
    }
}
