<?php

namespace App\Domains\Material\Engine;

use Illuminate\Support\Collection;

/**
 * StateEvaluator - Component 1 of MaterialLawEngine
 * 
 * Purpose: Measure world state without making decisions.
 * Rule: Only measure, never decide.
 */
class StateEvaluator
{
    /**
     * Evaluate current world state from material instances.
     * 
     * @param Collection $instances Collection of MaterialInstance
     * @return array State measurements
     */
    public function evaluate(Collection $instances): array
    {
        return [
            'pressure_levels' => $this->calculatePressureLevels($instances),
            'domain_states' => $this->calculateDomainStates($instances),
            'material_count' => [
                'total' => $instances->count(),
                'active' => $instances->where('retired_at', null)->count(),
                'retired' => $instances->whereNotNull('retired_at')->count(),
            ],
        ];
    }

    /**
     * Calculate pressure levels from active materials.
     * Scale: 0.0 - 1.0
     */
    private function calculatePressureLevels(Collection $instances): array
    {
        $pressures = [];
        $activeInstances = $instances->where('retired_at', null);

        // Economy pressures
        $pressures['subsistence'] = $this->getMaterialStrength($activeInstances, 'SUBSISTENCE_BASE');
        $pressures['inequality'] = $this->getMaterialStrength($activeInstances, 'INEQUALITY_GRADIENT');
        $pressures['resource_conflict'] = $this->getMaterialStrength($activeInstances, 'RESOURCE_CONFLICT_PRESSURE');
        $pressures['famine_risk'] = $this->getMaterialStrength($activeInstances, 'FAMINE_TRIGGER');

        // Memory pressures
        $pressures['trauma_density'] = $this->getMaterialStrength($activeInstances, 'TRAUMA_ENCODING');
        $pressures['nostalgia'] = $this->getMaterialStrength($activeInstances, 'NOSTALGIA_PRESSURE');
        $pressures['grievance'] = $this->getMaterialStrength($activeInstances, 'GRIEVANCE_ACCUMULATION');
        $pressures['identity_rigidity'] = $this->getMaterialStrength($activeInstances, 'IDENTITY_FOSSILIZATION');

        // Technology pressures
        $pressures['infrastructure_integrity'] = $this->getMaterialStrength($activeInstances, 'PRODUCTION_INFRASTRUCTURE');
        $pressures['tech_decay'] = $this->getMaterialStrength($activeInstances, 'SKILL_ATTRITION');
        $pressures['innovation_friction'] = $this->getMaterialStrength($activeInstances, 'INNOVATION_FRICTION');

        // Interaction pressures
        $pressures['external_threat'] = $this->getMaterialStrength($activeInstances, 'EXTERNAL_THREAT_PRESSURE');
        $pressures['migration'] = $this->getMaterialStrength($activeInstances, 'MIGRATION_PRESSURE');
        $pressures['cultural_friction'] = $this->getMaterialStrength($activeInstances, 'CULTURAL_FRICTION');

        return $pressures;
    }

    /**
     * Calculate aggregate state by domain.
     */
    private function calculateDomainStates(Collection $instances): array
    {
        $activeInstances = $instances->where('retired_at', null);

        return [
            'economy' => $this->calculateDomainAverage($activeInstances, ['institutional', 'behavioral'], 'economy'),
            'memory' => $this->calculateDomainAverage($activeInstances, ['symbolic'], 'memory'),
            'technology' => $this->calculateDomainAverage($activeInstances, ['institutional'], 'technology'),
            'interaction' => $this->calculateDomainAverage($activeInstances, ['behavioral', 'symbolic'], 'interaction'),
        ];
    }

    /**
     * Get material strength or 0 if not found.
     */
    private function getMaterialStrength(Collection $instances, string $materialCode): float
    {
        $instance = $instances->firstWhere('material.code', $materialCode);
        
        if (!$instance || $instance->retired_at) {
            return 0.0;
        }

        return min(1.0, max(0.0, $instance->strength_level / 10.0));
    }

    /**
     * Calculate average strength for materials in a domain.
     */
    private function calculateDomainAverage(Collection $instances, array $ontologies, string $domain): float
    {
        $domainInstances = $instances->filter(function ($instance) use ($ontologies, $domain) {
            // Simple domain detection based on material code patterns
            $code = $instance->material->code ?? '';
            
            // Economy domain
            if ($domain === 'economy' && in_array($code, [
                'SUBSISTENCE_BASE', 'RESOURCE_CONCENTRATION', 'SEASONAL_STABILITY',
                'LABOR_ORGANIZATION', 'PRODUCTIVITY_CEILING', 'SPECIALIZATION_DEPTH',
                'SURPLUS_DISTRIBUTION', 'INEQUALITY_GRADIENT', 'DEPENDENCY_CHAINS',
                'FAMINE_TRIGGER', 'RESOURCE_CONFLICT_PRESSURE', 'SURVIVAL_ADAPTATION'
            ])) {
                return true;
            }

            // Memory domain
            if ($domain === 'memory' && in_array($code, [
                'CANONICAL_HISTORY', 'SELECTIVE_MEMORY', 'ORAL_WRITTEN_RATIO',
                'MYTH_REINTERPRETATION_PRESSURE', 'HISTORICAL_REVISIONISM', 'TRAUMA_ENCODING',
                'RITUALIZED_REMEMBRANCE', 'INSTITUTIONAL_EDUCATION', 'ARTIFACT_ANCHORING',
                'GRIEVANCE_ACCUMULATION', 'NOSTALGIA_PRESSURE', 'IDENTITY_FOSSILIZATION'
            ])) {
                return true;
            }

            // Technology domain
            if ($domain === 'technology' && in_array($code, [
                'TRANSPORTATION_NETWORK', 'PRODUCTION_INFRASTRUCTURE', 'ENERGY_SOURCE',
                'TECHNICAL_LITERACY', 'KNOWLEDGE_PRESERVATION', 'INNOVATION_FRICTION',
                'INFRASTRUCTURE_CENTRALIZATION', 'MAINTENANCE_BURDEN', 'TECHNOLOGICAL_LOCK_IN',
                'INFRASTRUCTURE_COLLAPSE_TRIGGER', 'SKILL_ATTRITION', 'TECH_MYTHOLOGIZATION'
            ])) {
                return true;
            }

            // Interaction domain
            if ($domain === 'interaction' && in_array($code, [
                'MIGRATION_PRESSURE', 'TRADE_ROUTE_EXPOSURE', 'KNOWLEDGE_DIFFUSION_RATE',
                'CULTURAL_FRICTION', 'EXTERNAL_THREAT_PRESSURE', 'INVASION_CAPABILITY',
                'CULTURAL_DOMINANCE', 'ECONOMIC_DEPENDENCY', 'POLITICAL_ENTANGLEMENT',
                'SHARED_TRAUMA', 'COMPARATIVE_IDENTITY', 'WORLD_REPUTATION'
            ])) {
                return true;
            }

            return false;
        });

        if ($domainInstances->isEmpty()) {
            return 0.5; // Neutral state
        }

        $avgStrength = $domainInstances->avg('strength_level') / 10.0;
        return min(1.0, max(0.0, $avgStrength));
    }
}
