<?php

namespace App\Domains\Material\Engine;

use App\Domains\Material\MaterialInstance;
use Illuminate\Support\Collection;

class CrossDomainPressureCalculator
{
    /**
     * Calculate cross-domain pressures between Economy and Memory materials.
     * 
     * This implements the critical feedback loops:
     * - Economy → Memory (famine → trauma, inequality → grievance)
     * - Memory → Economy (nostalgia → regression, identity → rigidity)
     */
    public function calculate(Collection $instances): array
    {
        $pressures = [];

        // Economy → Memory pressures
        $pressures = array_merge($pressures, $this->economyToMemory($instances));

        // Memory → Economy pressures
        $pressures = array_merge($pressures, $this->memoryToEconomy($instances));

        // Technology → Economy pressures
        $pressures = array_merge($pressures, $this->technologyToEconomy($instances));

        // Economy → Technology pressures
        $pressures = array_merge($pressures, $this->economyToTechnology($instances));

        // Interaction → All domains
        $pressures = array_merge($pressures, $this->interactionToOthers($instances));

        // All domains → Interaction
        $pressures = array_merge($pressures, $this->othersToInteraction($instances));

        return $pressures;
    }

    /**
     * Economy domain affecting Memory domain.
     */
    private function economyToMemory(Collection $instances): array
    {
        $pressures = [];

        // Famine → Trauma Encoding
        $famine = $instances->firstWhere('material.code', 'FAMINE_TRIGGER');
        if ($famine && !$famine->retired_at && $famine->strength_level > 5) {
            $pressures[] = [
                'source' => 'FAMINE_TRIGGER',
                'target' => 'TRAUMA_ENCODING',
                'type' => 'activation',
                'strength' => $famine->strength_level * 0.8,
                'description' => 'Famine creates collective trauma encoding',
            ];
        }

        // Inequality → Grievance Accumulation
        $inequality = $instances->firstWhere('material.code', 'INEQUALITY_GRADIENT');
        if ($inequality && !$inequality->retired_at && $inequality->strength_level > 6) {
            $pressures[] = [
                'source' => 'INEQUALITY_GRADIENT',
                'target' => 'GRIEVANCE_ACCUMULATION',
                'type' => 'amplification',
                'strength' => $inequality->strength_level * 0.7,
                'description' => 'Economic inequality fuels historical grievances',
            ];
        }

        // Resource Conflict → Selective Memory (denial)
        $conflict = $instances->firstWhere('material.code', 'RESOURCE_CONFLICT_PRESSURE');
        if ($conflict && !$conflict->retired_at && $conflict->strength_level > 5) {
            $pressures[] = [
                'source' => 'RESOURCE_CONFLICT_PRESSURE',
                'target' => 'SELECTIVE_MEMORY',
                'type' => 'activation',
                'strength' => $conflict->strength_level * 0.6,
                'description' => 'Resource conflicts create selective historical memory',
            ];
        }

        // Subsistence Crisis → Nostalgia Pressure
        $subsistence = $instances->firstWhere('material.code', 'SUBSISTENCE_BASE');
        if ($subsistence && !$subsistence->retired_at && $subsistence->strength_level < 4) {
            $pressures[] = [
                'source' => 'SUBSISTENCE_BASE',
                'target' => 'NOSTALGIA_PRESSURE',
                'type' => 'activation',
                'strength' => (10 - $subsistence->strength_level) * 0.5,
                'description' => 'Economic decline triggers nostalgia for golden age',
            ];
        }

        return $pressures;
    }

    /**
     * Memory domain affecting Economy domain.
     */
    private function memoryToEconomy(Collection $instances): array
    {
        $pressures = [];

        // Nostalgia → Productivity Ceiling (regression)
        $nostalgia = $instances->firstWhere('material.code', 'NOSTALGIA_PRESSURE');
        if ($nostalgia && !$nostalgia->retired_at && $nostalgia->strength_level > 6) {
            $pressures[] = [
                'source' => 'NOSTALGIA_PRESSURE',
                'target' => 'PRODUCTIVITY_CEILING',
                'type' => 'suppression',
                'strength' => $nostalgia->strength_level * 0.6,
                'description' => 'Nostalgia for past suppresses productivity innovation',
            ];
        }

        // Identity Fossilization → Labor Organization (rigidity)
        $fossilization = $instances->firstWhere('material.code', 'IDENTITY_FOSSILIZATION');
        if ($fossilization && !$fossilization->retired_at && $fossilization->strength_level > 7) {
            $pressures[] = [
                'source' => 'IDENTITY_FOSSILIZATION',
                'target' => 'LABOR_ORGANIZATION',
                'type' => 'rigidification',
                'strength' => $fossilization->strength_level * 0.7,
                'description' => 'Fossilized identity prevents labor system adaptation',
            ];
        }

        // Grievance Accumulation → Resource Conflict Pressure
        $grievance = $instances->firstWhere('material.code', 'GRIEVANCE_ACCUMULATION');
        if ($grievance && !$grievance->retired_at && $grievance->strength_level > 7) {
            $pressures[] = [
                'source' => 'GRIEVANCE_ACCUMULATION',
                'target' => 'RESOURCE_CONFLICT_PRESSURE',
                'type' => 'amplification',
                'strength' => $grievance->strength_level * 0.8,
                'description' => 'Historical grievances intensify resource conflicts',
            ];
        }

        // Canonical History → Surplus Distribution (legitimation)
        $canonical = $instances->firstWhere('material.code', 'CANONICAL_HISTORY');
        if ($canonical && !$canonical->retired_at && $canonical->strength_level > 6) {
            $pressures[] = [
                'source' => 'CANONICAL_HISTORY',
                'target' => 'SURPLUS_DISTRIBUTION',
                'type' => 'legitimation',
                'strength' => $canonical->strength_level * 0.5,
                'description' => 'Official history legitimates distribution patterns',
            ];
        }

        // Trauma Encoding → Specialization Depth (avoidance)
        $trauma = $instances->firstWhere('material.code', 'TRAUMA_ENCODING');
        if ($trauma && !$trauma->retired_at && $trauma->strength_level > 7) {
            $pressures[] = [
                'source' => 'TRAUMA_ENCODING',
                'target' => 'SPECIALIZATION_DEPTH',
                'type' => 'suppression',
                'strength' => $trauma->strength_level * 0.4,
                'description' => 'Collective trauma creates economic conservatism',
            ];
        }

        return $pressures;
    }
    /**
     * Technology domain affecting Economy domain.
     */
    private function technologyToEconomy(Collection $instances): array
    {
        // TODO: Implement technology → economy pressure logic
        return [];
    }

    /**
     * Economy domain affecting Technology domain.
     */
    private function economyToTechnology(Collection $instances): array
    {
        // TODO: Implement economy → technology pressure logic
        return [];
    }

    /**
     * Interaction domain affecting all other domains.
     */
    private function interactionToOthers(Collection $instances): array
    {
        // TODO: Implement interaction → others pressure logic
        return [];
    }

    /**
     * All domains affecting Interaction domain.
     */
    private function othersToInteraction(Collection $instances): array
    {
        // TODO: Implement others → interaction pressure logic
        return [];
    }

    /**
     * Apply calculated pressures to material instances.
     */
    public function applyPressures(array $pressures, Collection $instances): array
    {
        $effects = [];

        foreach ($pressures as $pressure) {
            $target = $instances->firstWhere('material.code', $pressure['target']);
            
            if (!$target) {
                continue; // Target material not instantiated
            }

            switch ($pressure['type']) {
                case 'activation':
                    if ($target->activation_epoch === null && $pressure['strength'] > 5) {
                        $effects[] = [
                            'type' => 'cross_domain_activation',
                            'source' => $pressure['source'],
                            'target' => $pressure['target'],
                            'description' => $pressure['description'],
                        ];
                    }
                    break;

                case 'amplification':
                    if (!$target->retired_at) {
                        $effects[] = [
                            'type' => 'cross_domain_amplification',
                            'source' => $pressure['source'],
                            'target' => $pressure['target'],
                            'strength_delta' => min(2, $pressure['strength'] * 0.2),
                            'description' => $pressure['description'],
                        ];
                    }
                    break;

                case 'suppression':
                    if (!$target->retired_at) {
                        $effects[] = [
                            'type' => 'cross_domain_suppression',
                            'source' => $pressure['source'],
                            'target' => $pressure['target'],
                            'strength_delta' => -min(2, $pressure['strength'] * 0.2),
                            'description' => $pressure['description'],
                        ];
                    }
                    break;

                case 'rigidification':
                case 'legitimation':
                    $effects[] = [
                        'type' => 'cross_domain_' . $pressure['type'],
                        'source' => $pressure['source'],
                        'target' => $pressure['target'],
                        'description' => $pressure['description'],
                    ];
                    break;
            }
        }

        return $effects;
    }
}
