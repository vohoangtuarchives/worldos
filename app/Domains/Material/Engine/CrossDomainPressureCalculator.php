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
        $pressures = [];

        // Transportation Network → Surplus Distribution (market access)
        $transport = $instances->firstWhere('material.code', 'TRANSPORTATION_NETWORK');
        if ($transport && !$transport->retired_at && $transport->strength_level > 6) {
            $pressures[] = [
                'source' => 'TRANSPORTATION_NETWORK',
                'target' => 'SURPLUS_DISTRIBUTION',
                'type' => 'amplification',
                'strength' => $transport->strength_level * 0.7,
                'description' => 'Transportation network improves market efficiency and surplus distribution',
            ];
        }

        // Production Infrastructure → Productivity Ceiling (output capacity)
        $production = $instances->firstWhere('material.code', 'PRODUCTION_INFRASTRUCTURE');
        if ($production && !$production->retired_at && $production->strength_level > 5) {
            $pressures[] = [
                'source' => 'PRODUCTION_INFRASTRUCTURE',
                'target' => 'PRODUCTIVITY_CEILING',
                'type' => 'amplification',
                'strength' => $production->strength_level * 0.8,
                'description' => 'Production infrastructure raises productivity ceiling',
            ];
        }

        // Energy Source → Specialization Depth (energy availability)
        $energy = $instances->firstWhere('material.code', 'ENERGY_SOURCE');
        if ($energy && !$energy->retired_at && $energy->strength_level > 7) {
            $pressures[] = [
                'source' => 'ENERGY_SOURCE',
                'target' => 'SPECIALIZATION_DEPTH',
                'type' => 'amplification',
                'strength' => $energy->strength_level * 0.6,
                'description' => 'Abundant energy enables deeper specialization',
            ];
        }

        // Technical Literacy → Labor Organization (skilled workforce)
        $literacy = $instances->firstWhere('material.code', 'TECHNICAL_LITERACY');
        if ($literacy && !$literacy->retired_at && $literacy->strength_level > 6) {
            $pressures[] = [
                'source' => 'TECHNICAL_LITERACY',
                'target' => 'LABOR_ORGANIZATION',
                'type' => 'amplification',
                'strength' => $literacy->strength_level * 0.5,
                'description' => 'Technical literacy improves labor organization efficiency',
            ];
        }

        // Innovation Friction → Productivity Ceiling (resistance to change)
        $friction = $instances->firstWhere('material.code', 'INNOVATION_FRICTION');
        if ($friction && !$friction->retired_at && $friction->strength_level > 7) {
            $pressures[] = [
                'source' => 'INNOVATION_FRICTION',
                'target' => 'PRODUCTIVITY_CEILING',
                'type' => 'suppression',
                'strength' => $friction->strength_level * 0.6,
                'description' => 'Innovation friction suppresses productivity growth',
            ];
        }

        // Maintenance Burden → Subsistence Base (resource drain)
        $maintenance = $instances->firstWhere('material.code', 'MAINTENANCE_BURDEN');
        if ($maintenance && !$maintenance->retired_at && $maintenance->strength_level > 7) {
            $pressures[] = [
                'source' => 'MAINTENANCE_BURDEN',
                'target' => 'SUBSISTENCE_BASE',
                'type' => 'suppression',
                'strength' => $maintenance->strength_level * 0.5,
                'description' => 'High maintenance burden drains subsistence resources',
            ];
        }

        // Infrastructure Collapse → Subsistence Base (systemic failure)
        $collapse = $instances->firstWhere('material.code', 'INFRASTRUCTURE_COLLAPSE_TRIGGER');
        if ($collapse && !$collapse->retired_at) {
            $pressures[] = [
                'source' => 'INFRASTRUCTURE_COLLAPSE_TRIGGER',
                'target' => 'SUBSISTENCE_BASE',
                'type' => 'suppression',
                'strength' => $collapse->strength_level * 0.9,
                'description' => 'Infrastructure collapse devastates subsistence base',
            ];
        }

        return $pressures;
    }

    /**
     * Economy domain affecting Technology domain.
     */
    private function economyToTechnology(Collection $instances): array
    {
        $pressures = [];

        // Surplus Distribution → Production Infrastructure (investment capacity)
        $surplus = $instances->firstWhere('material.code', 'SURPLUS_DISTRIBUTION');
        if ($surplus && !$surplus->retired_at && $surplus->strength_level > 6) {
            $pressures[] = [
                'source' => 'SURPLUS_DISTRIBUTION',
                'target' => 'PRODUCTION_INFRASTRUCTURE',
                'type' => 'amplification',
                'strength' => $surplus->strength_level * 0.6,
                'description' => 'Surplus enables investment in production infrastructure',
            ];
        }

        // Specialization Depth → Technical Literacy (skill development)
        $specialization = $instances->firstWhere('material.code', 'SPECIALIZATION_DEPTH');
        if ($specialization && !$specialization->retired_at && $specialization->strength_level > 7) {
            $pressures[] = [
                'source' => 'SPECIALIZATION_DEPTH',
                'target' => 'TECHNICAL_LITERACY',
                'type' => 'amplification',
                'strength' => $specialization->strength_level * 0.5,
                'description' => 'Deep specialization drives technical literacy development',
            ];
        }

        // Labor Organization → Knowledge Preservation (institutional support)
        $labor = $instances->firstWhere('material.code', 'LABOR_ORGANIZATION');
        if ($labor && !$labor->retired_at && $labor->strength_level > 6) {
            $pressures[] = [
                'source' => 'LABOR_ORGANIZATION',
                'target' => 'KNOWLEDGE_PRESERVATION',
                'type' => 'amplification',
                'strength' => $labor->strength_level * 0.4,
                'description' => 'Organized labor supports knowledge preservation systems',
            ];
        }

        // Resource Conflict Pressure → Innovation Friction (conservatism)
        $conflict = $instances->firstWhere('material.code', 'RESOURCE_CONFLICT_PRESSURE');
        if ($conflict && !$conflict->retired_at && $conflict->strength_level > 7) {
            $pressures[] = [
                'source' => 'RESOURCE_CONFLICT_PRESSURE',
                'target' => 'INNOVATION_FRICTION',
                'type' => 'amplification',
                'strength' => $conflict->strength_level * 0.6,
                'description' => 'Resource scarcity increases innovation friction and conservatism',
            ];
        }

        // Subsistence Crisis → Infrastructure Collapse (neglect)
        $subsistence = $instances->firstWhere('material.code', 'SUBSISTENCE_BASE');
        if ($subsistence && !$subsistence->retired_at && $subsistence->strength_level < 3) {
            $pressures[] = [
                'source' => 'SUBSISTENCE_BASE',
                'target' => 'INFRASTRUCTURE_COLLAPSE_TRIGGER',
                'type' => 'activation',
                'strength' => (10 - $subsistence->strength_level) * 0.7,
                'description' => 'Subsistence crisis leads to infrastructure neglect and collapse',
            ];
        }

        // Inequality Gradient → Innovation Friction (social resistance)
        $inequality = $instances->firstWhere('material.code', 'INEQUALITY_GRADIENT');
        if ($inequality && !$inequality->retired_at && $inequality->strength_level > 8) {
            $pressures[] = [
                'source' => 'INEQUALITY_GRADIENT',
                'target' => 'INNOVATION_FRICTION',
                'type' => 'amplification',
                'strength' => $inequality->strength_level * 0.5,
                'description' => 'High inequality creates social resistance to technological change',
            ];
        }

        return $pressures;
    }

    /**
     * Interaction domain affecting all other domains.
     */
    private function interactionToOthers(Collection $instances): array
    {
        $pressures = [];

        // Migration Pressure → Cultural Friction (identity stress)
        $migration = $instances->firstWhere('material.code', 'MIGRATION_PRESSURE');
        if ($migration && !$migration->retired_at && $migration->strength_level > 6) {
            $pressures[] = [
                'source' => 'MIGRATION_PRESSURE',
                'target' => 'CULTURAL_FRICTION',
                'type' => 'amplification',
                'strength' => $migration->strength_level * 0.7,
                'description' => 'Migration pressure increases cultural friction and identity stress',
            ];
        }

        // Trade Route Exposure → Technical Literacy (knowledge transfer)
        $trade = $instances->firstWhere('material.code', 'TRADE_ROUTE_EXPOSURE');
        if ($trade && !$trade->retired_at && $trade->strength_level > 5) {
            $pressures[] = [
                'source' => 'TRADE_ROUTE_EXPOSURE',
                'target' => 'TECHNICAL_LITERACY',
                'type' => 'amplification',
                'strength' => $trade->strength_level * 0.6,
                'description' => 'Trade routes facilitate technical literacy and knowledge transfer',
            ];
        }

        // Trade Route Exposure → Innovation Friction (foreign competition)
        if ($trade && !$trade->retired_at && $trade->strength_level > 7) {
            $pressures[] = [
                'source' => 'TRADE_ROUTE_EXPOSURE',
                'target' => 'INNOVATION_FRICTION',
                'type' => 'suppression',
                'strength' => $trade->strength_level * 0.4,
                'description' => 'High trade exposure can create defensive innovation friction',
            ];
        }

        // Knowledge Diffusion Rate → Innovation Friction (rapid change)
        $diffusion = $instances->firstWhere('material.code', 'KNOWLEDGE_DIFFUSION_RATE');
        if ($diffusion && !$diffusion->retired_at && $diffusion->strength_level > 8) {
            $pressures[] = [
                'source' => 'KNOWLEDGE_DIFFUSION_RATE',
                'target' => 'INNOVATION_FRICTION',
                'type' => 'suppression',
                'strength' => $diffusion->strength_level * 0.3,
                'description' => 'Rapid knowledge diffusion can overwhelm local innovation capacity',
            ];
        }

        // Cultural Friction → Identity Fossilization (defensive conservatism)
        $friction = $instances->firstWhere('material.code', 'CULTURAL_FRICTION');
        if ($friction && !$friction->retired_at && $friction->strength_level > 7) {
            $pressures[] = [
                'source' => 'CULTURAL_FRICTION',
                'target' => 'IDENTITY_FOSSILIZATION',
                'type' => 'amplification',
                'strength' => $friction->strength_level * 0.6,
                'description' => 'Cultural friction drives identity fossilization as defensive response',
            ];
        }

        // External Threat Pressure → Infrastructure Centralization (defensive consolidation)
        $threat = $instances->firstWhere('material.code', 'EXTERNAL_THREAT_PRESSURE');
        if ($threat && !$threat->retired_at && $threat->strength_level > 6) {
            $pressures[] = [
                'source' => 'EXTERNAL_THREAT_PRESSURE',
                'target' => 'INFRASTRUCTURE_CENTRALIZATION',
                'type' => 'amplification',
                'strength' => $threat->strength_level * 0.7,
                'description' => 'External threats drive infrastructure centralization for defense',
            ];
        }

        // Cultural Dominance → Canonical History (cultural imposition)
        $dominance = $instances->firstWhere('material.code', 'CULTURAL_DOMINANCE');
        if ($dominance && !$dominance->retired_at && $dominance->strength_level > 7) {
            $pressures[] = [
                'source' => 'CULTURAL_DOMINANCE',
                'target' => 'CANONICAL_HISTORY',
                'type' => 'amplification',
                'strength' => $dominance->strength_level * 0.5,
                'description' => 'Cultural dominance imposes canonical historical narratives',
            ];
        }

        // Economic Dependency → Grievance Accumulation (exploitation resentment)
        $dependency = $instances->firstWhere('material.code', 'ECONOMIC_DEPENDENCY');
        if ($dependency && !$dependency->retired_at && $dependency->strength_level > 7) {
            $pressures[] = [
                'source' => 'ECONOMIC_DEPENDENCY',
                'target' => 'GRIEVANCE_ACCUMULATION',
                'type' => 'amplification',
                'strength' => $dependency->strength_level * 0.6,
                'description' => 'Economic dependency creates grievances and exploitation resentment',
            ];
        }

        return $pressures;
    }

    /**
     * All domains affecting Interaction domain.
     */
    private function othersToInteraction(Collection $instances): array
    {
        $pressures = [];

        // Famine Trigger → Migration Pressure (famine flight)
        $famine = $instances->firstWhere('material.code', 'FAMINE_TRIGGER');
        if ($famine && !$famine->retired_at && $famine->strength_level > 6) {
            $pressures[] = [
                'source' => 'FAMINE_TRIGGER',
                'target' => 'MIGRATION_PRESSURE',
                'type' => 'amplification',
                'strength' => $famine->strength_level * 0.8,
                'description' => 'Famine triggers migration pressure as populations flee',
            ];
        }

        // Resource Conflict Pressure → External Threat Pressure (escalation)
        $conflict = $instances->firstWhere('material.code', 'RESOURCE_CONFLICT_PRESSURE');
        if ($conflict && !$conflict->retired_at && $conflict->strength_level > 7) {
            $pressures[] = [
                'source' => 'RESOURCE_CONFLICT_PRESSURE',
                'target' => 'EXTERNAL_THREAT_PRESSURE',
                'type' => 'amplification',
                'strength' => $conflict->strength_level * 0.6,
                'description' => 'Resource conflicts escalate external threat perceptions',
            ];
        }

        // Surplus Distribution → Trade Route Exposure (trade capacity)
        $surplus = $instances->firstWhere('material.code', 'SURPLUS_DISTRIBUTION');
        if ($surplus && !$surplus->retired_at && $surplus->strength_level > 6) {
            $pressures[] = [
                'source' => 'SURPLUS_DISTRIBUTION',
                'target' => 'TRADE_ROUTE_EXPOSURE',
                'type' => 'amplification',
                'strength' => $surplus->strength_level * 0.7,
                'description' => 'Surplus enables trade route expansion and exposure',
            ];
        }

        // Transportation Network → Trade Route Exposure (connectivity)
        $transport = $instances->firstWhere('material.code', 'TRANSPORTATION_NETWORK');
        if ($transport && !$transport->retired_at && $transport->strength_level > 5) {
            $pressures[] = [
                'source' => 'TRANSPORTATION_NETWORK',
                'target' => 'TRADE_ROUTE_EXPOSURE',
                'type' => 'amplification',
                'strength' => $transport->strength_level * 0.8,
                'description' => 'Transportation networks enable trade route development',
            ];
        }

        // Identity Fossilization → Cultural Friction (rigid boundaries)
        $fossilization = $instances->firstWhere('material.code', 'IDENTITY_FOSSILIZATION');
        if ($fossilization && !$fossilization->retired_at && $fossilization->strength_level > 7) {
            $pressures[] = [
                'source' => 'IDENTITY_FOSSILIZATION',
                'target' => 'CULTURAL_FRICTION',
                'type' => 'amplification',
                'strength' => $fossilization->strength_level * 0.6,
                'description' => 'Fossilized identity creates cultural friction with outsiders',
            ];
        }

        // Grievance Accumulation → External Threat Pressure (paranoia)
        $grievance = $instances->firstWhere('material.code', 'GRIEVANCE_ACCUMULATION');
        if ($grievance && !$grievance->retired_at && $grievance->strength_level > 8) {
            $pressures[] = [
                'source' => 'GRIEVANCE_ACCUMULATION',
                'target' => 'EXTERNAL_THREAT_PRESSURE',
                'type' => 'amplification',
                'strength' => $grievance->strength_level * 0.5,
                'description' => 'Accumulated grievances create external threat paranoia',
            ];
        }

        // Canonical History → Cultural Dominance (legitimacy)
        $canonical = $instances->firstWhere('material.code', 'CANONICAL_HISTORY');
        if ($canonical && !$canonical->retired_at && $canonical->strength_level > 7) {
            $pressures[] = [
                'source' => 'CANONICAL_HISTORY',
                'target' => 'CULTURAL_DOMINANCE',
                'type' => 'amplification',
                'strength' => $canonical->strength_level * 0.4,
                'description' => 'Canonical history provides legitimacy for cultural dominance',
            ];
        }

        // Knowledge Preservation → Knowledge Diffusion Rate (sharing capacity)
        $preservation = $instances->firstWhere('material.code', 'KNOWLEDGE_PRESERVATION');
        if ($preservation && !$preservation->retired_at && $preservation->strength_level > 6) {
            $pressures[] = [
                'source' => 'KNOWLEDGE_PRESERVATION',
                'target' => 'KNOWLEDGE_DIFFUSION_RATE',
                'type' => 'amplification',
                'strength' => $preservation->strength_level * 0.5,
                'description' => 'Knowledge preservation systems enable knowledge diffusion',
            ];
        }

        // Innovation Friction → Cultural Friction (resistance to foreign ideas)
        $friction = $instances->firstWhere('material.code', 'INNOVATION_FRICTION');
        if ($friction && !$friction->retired_at && $friction->strength_level > 7) {
            $pressures[] = [
                'source' => 'INNOVATION_FRICTION',
                'target' => 'CULTURAL_FRICTION',
                'type' => 'amplification',
                'strength' => $friction->strength_level * 0.4,
                'description' => 'Innovation friction extends to cultural friction with foreign ideas',
            ];
        }

        return $pressures;
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
