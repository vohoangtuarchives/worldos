<?php

namespace Tuzy\Application\World\Interaction;

class InteractionZone
{
    public function __construct(
        public array $worlds,
        public $zone_coherence,
        public array $dominant_narratives,
        public array $active_interactions
    ) {}

    public function calculateZoneEvolution(): array
    {
        $evolution = [
            'coherence_delta' => 0,
            'entropy_delta' => 0,
            'belief_mutation' => [],
            'resource_exchange' => [],
            'stability_shift' => 0
        ];

        // Calculate average metrics across zone
        $avgCoherence = $this->zone_coherence;
        $avgEntropy = array_sum(array_map(fn($w) => $w->entropy, $this->worlds)) / count($this->worlds);
        $avgDominance = array_sum(array_map(fn($w) => $w->dominanceLevel, $this->worlds)) / count($this->worlds);

        // Zone coherence affects all worlds
        if ($avgCoherence > 0.8) {
            // High coherence zone: stabilizing effect
            $evolution['coherence_delta'] = 0.05;
            $evolution['entropy_delta'] = -0.02;
        } elseif ($avgCoherence < 0.4) {
            // Low coherence zone: destabilizing effect
            $evolution['coherence_delta'] = -0.08;
            $evolution['entropy_delta'] = 0.12;
        }

        // Strong interactions create mutations
        foreach ($this->active_interactions as $interaction) {
            if ($interaction['strength'] > 0.7) {
                $evolution['belief_mutation'][] = [
                    'type' => $interaction['type'],
                    'strength' => $interaction['strength'],
                    'worlds' => [$interaction['world_a'], $interaction['world_b']]
                ];
            }
        }

        // Resource exchange based on dominant narratives
        if (in_array('resource', $this->dominant_narratives)) {
            $evolution['resource_exchange'][] = [
                'type' => 'RESOURCE_CROSSFLOW',
                'amount' => $avgDominance * 0.3,
                'efficiency' => $avgCoherence
            ];
        }

        return $evolution;
    }

    public function detectZoneCollapse(): bool
    {
        // Zone collapses if:
        // 1. Average entropy > 0.8
        // 2. Zone coherence < 0.3
        // 3. More than 50% of worlds in critical state

        $avgEntropy = array_sum(array_map(fn($w) => $w->entropy, $this->worlds)) / count($this->worlds);
        $criticalWorlds = array_filter(
            $this->worlds,
            fn($w) => $w->entropy > 0.8 || $w->coherence < 0.3
        );

        return (
            $avgEntropy > 0.8 ||
            $this->zone_coherence < 0.3 ||
            (count($criticalWorlds) / count($this->worlds)) > 0.5
        );
    }

    public function spawnZoneEvents(): array
    {
        $events = [];

        // High interaction strength events
        foreach ($this->active_interactions as $interaction) {
            if ($interaction['strength'] > 0.9) {
                $events[] = [
                    'type' => 'REALITY_DISTORTION',
                    'worlds' => [$interaction['world_a'], $interaction['world_b']],
                    'severity' => $interaction['strength'],
                    'description' => 'Strong narrative boundary breach detected'
                ];
            }
        }

        // Zone-level events
        if ($this->zone_coherence < 0.2) {
            $events[] = [
                'description' => 'Interaction zone has collapsed',
                'zone_worlds' => $this->getWorldIds(),
                'severity' => 1.0 - $this->zone_coherence,
            ];
        }

        // Hybrid emergence events
        if (count($this->dominant_narratives) >= 3 && $this->zone_coherence > 0.6) {
            $events[] = [
                'type' => 'HYBRID_EMERGENCE',
                'worlds' => array_map(fn($w) => $w->id, $this->worlds),
                'severity' => $this->zone_coherence,
                'description' => 'Multiple narrative convergence detected',
                'hybrid_candidates' => $this->generateHybridCandidates()
            ];
        }

        return $events;
    }

    private function generateHybridCandidates(): array
    {
        $candidates = [];
        $narratives = $this->dominant_narratives;

        for ($i = 0; $i < count($narratives); $i++) {
            for ($j = $i + 1; $j < count($narratives); $j++) {
                $candidates[] = [
                    'preset_a' => $narratives[$i],
                    'preset_b' => $narratives[$j],
                    'compatibility' => $this->calculateHybridCompatibility($narratives[$i], $narratives[$j])
                ];
            }
        }

        // Sort by compatibility and return top candidates
        usort($candidates, fn($a, $b) => $b['compatibility'] <=> $a['compatibility']);
        return array_slice($candidates, 0, 3);
    }

    private function calculateHybridCompatibility(string $presetA, string $presetB): float
    {
        // Compatibility matrix for hybrid generation
        $compatibilityMatrix = [
            'faith' => [
                'rational' => 0.3,
                'political' => 0.7,
                'resource' => 0.5,
                'chaotic' => 0.2
            ],
            'rational' => [
                'faith' => 0.3,
                'political' => 0.6,
                'resource' => 0.8,
                'chaotic' => 0.4
            ],
            'political' => [
                'faith' => 0.7,
                'rational' => 0.6,
                'resource' => 0.9,
                'chaotic' => 0.5
            ],
            'resource' => [
                'faith' => 0.5,
                'rational' => 0.8,
                'political' => 0.9,
                'chaotic' => 0.6
            ],
            'chaotic' => [
                'faith' => 0.2,
                'rational' => 0.4,
                'political' => 0.5,
                'resource' => 0.6
            ]
        ];

        return $compatibilityMatrix[$presetA][$presetB] ?? 0.1;
    }

    public function getWorldIds(): array
    {
        return array_map(fn($w) => $w->id, $this->worlds);
    }

    public function getWorldCount(): int
    {
        return count($this->worlds);
    }

    public function getInteractionCount(): int
    {
        return count($this->active_interactions);
    }
}
