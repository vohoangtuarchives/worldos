<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Tuzy\Domain\Material\Material;
use Illuminate\Support\Facades\DB;

class EconomyMemoryMaterialsSeeder extends Seeder
{
    /**
     * Seed Economy/Survival and Memory/Reinterpretation materials.
     */
    public function run(): void
    {
        $materials = $this->getMaterials();

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['code' => $material['code']],
                $material
            );
        }

        $this->command->info('Economy & Memory materials seeded successfully (24 materials).');
    }

    private function getMaterials(): array
    {
        return [
            // ===== DOMAIN 1: ECONOMY/SURVIVAL =====
            
            // Group A: Resource Foundation
            [
                'code' => 'SUBSISTENCE_BASE',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'Foundation of food and resource production determining survival capacity. Low levels trigger famine risk, high levels enable population growth.',
                'origin_sources' => ['agricultural_systems', 'hunting_gathering', 'fishing_economies'],
                'preconditions' => null,
                'pressure_inputs' => ['population', 'climate', 'technology'],
                'pressure_outputs' => ['famine_risk', 'population_growth', 'expansion_pressure'],
                'incompatible_with' => null,
                'mutation_axes' => ['production_mode', 'resilience'],
            ],
            [
                'code' => 'RESOURCE_CONCENTRATION',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Degree of resource control by elites versus distributed access. High concentration drives elite emergence and class formation.',
                'origin_sources' => ['land_ownership', 'water_rights', 'mineral_control'],
                'preconditions' => null,
                'pressure_inputs' => ['subsistence_base', 'labor_organization'],
                'pressure_outputs' => ['elite_power', 'inequality', 'class_formation'],
                'incompatible_with' => ['GIFT_ECONOMY', 'COMMUNAL_SHARING'],
                'mutation_axes' => ['concentration_level'],
            ],
            [
                'code' => 'SEASONAL_STABILITY',
                'ontology' => 'behavioral',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'Predictability of seasonal resource cycles. Low stability increases ritualization and prophecy demand.',
                'origin_sources' => ['climate_patterns', 'agricultural_cycles'],
                'preconditions' => null,
                'pressure_inputs' => ['climate', 'subsistence_base'],
                'pressure_outputs' => ['ritual_pressure', 'prophecy_demand', 'anxiety'],
                'incompatible_with' => null,
                'mutation_axes' => ['stability_level'],
            ],

            // Group B: Production & Labor
            [
                'code' => 'LABOR_ORGANIZATION',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'How labor is organized and controlled. Variants include kin-based, coerced, and guild-like systems.',
                'origin_sources' => ['kinship_labor', 'slavery', 'corvee', 'guilds'],
                'preconditions' => null,
                'pressure_inputs' => ['population', 'resource_concentration'],
                'pressure_outputs' => ['rebellion_pressure', 'productivity', 'social_cohesion'],
                'incompatible_with' => null,
                'mutation_axes' => ['coercion_level', 'specialization'],
            ],
            [
                'code' => 'PRODUCTIVITY_CEILING',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Maximum output achievable with current technology and organization. Reaching ceiling triggers expansion or collapse.',
                'origin_sources' => ['technological_limits', 'organizational_capacity'],
                'preconditions' => null,
                'pressure_inputs' => ['technology', 'labor_organization', 'specialization_depth'],
                'pressure_outputs' => ['expansionism', 'collapse_risk', 'innovation_pressure'],
                'incompatible_with' => null,
                'mutation_axes' => ['ceiling_level'],
            ],
            [
                'code' => 'SPECIALIZATION_DEPTH',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Degree of labor specialization and interdependence. High specialization creates wealth but fragility.',
                'origin_sources' => ['craft_guilds', 'professional_classes'],
                'preconditions' => null,
                'pressure_inputs' => ['productivity_ceiling', 'population'],
                'pressure_outputs' => ['interdependence', 'fragility', 'wealth'],
                'incompatible_with' => ['ISOLATION_MYTH', 'SELF_SUFFICIENCY'],
                'mutation_axes' => ['specialization_level'],
            ],

            // Group C: Distribution & Inequality
            [
                'code' => 'SURPLUS_DISTRIBUTION',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'How surplus resources are allocated. Variants include ritual redistribution, elite hoarding, and communal storage.',
                'origin_sources' => ['potlatch', 'tribute_systems', 'granaries'],
                'preconditions' => null,
                'pressure_inputs' => ['subsistence_base', 'resource_concentration'],
                'pressure_outputs' => ['unrest', 'legitimacy', 'inequality'],
                'incompatible_with' => null,
                'mutation_axes' => ['redistribution_mode'],
            ],
            [
                'code' => 'INEQUALITY_GRADIENT',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Steepness of wealth and power inequality. High gradients activate revolt myths and increase naturally without counter-myths.',
                'origin_sources' => ['class_systems', 'wealth_gaps'],
                'preconditions' => null,
                'pressure_inputs' => ['resource_concentration', 'surplus_distribution'],
                'pressure_outputs' => ['revolt_risk', 'myth_activation', 'social_tension'],
                'incompatible_with' => ['EGALITARIAN_FOUNDING', 'COMMUNAL_EQUALITY'],
                'mutation_axes' => ['gradient_steepness'],
            ],
            [
                'code' => 'DEPENDENCY_CHAINS',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Length and complexity of resource dependency networks. Long chains create systemic fragility and cascade collapse risk.',
                'origin_sources' => ['trade_networks', 'supply_chains'],
                'preconditions' => null,
                'pressure_inputs' => ['specialization_depth', 'trade_volume'],
                'pressure_outputs' => ['systemic_fragility', 'collapse_cascade'],
                'incompatible_with' => null,
                'mutation_axes' => ['chain_length'],
            ],

            // Group D: Shock & Failure
            [
                'code' => 'FAMINE_TRIGGER',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Activation of famine conditions causing population drop and belief mutation.',
                'origin_sources' => ['crop_failure', 'drought', 'war_disruption'],
                'preconditions' => ['subsistence_base < 3'],
                'pressure_inputs' => ['subsistence_base', 'seasonal_stability'],
                'pressure_outputs' => ['population_drop', 'belief_mutation', 'migration'],
                'incompatible_with' => null,
                'mutation_axes' => null,
            ],
            [
                'code' => 'RESOURCE_CONFLICT_PRESSURE',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Pressure toward violent resource competition. Increases war likelihood and territorial expansion.',
                'origin_sources' => ['scarcity', 'competition'],
                'preconditions' => null,
                'pressure_inputs' => ['subsistence_base', 'population', 'inequality_gradient'],
                'pressure_outputs' => ['war_likelihood', 'territorial_expansion'],
                'incompatible_with' => null,
                'mutation_axes' => ['conflict_intensity'],
            ],
            [
                'code' => 'SURVIVAL_ADAPTATION',
                'ontology' => 'behavioral',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Cultural mutation in response to survival threats. Creates resilience and endurance narratives.',
                'origin_sources' => ['crisis_response', 'innovation'],
                'preconditions' => ['famine_trigger OR resource_conflict_pressure'],
                'pressure_inputs' => ['famine_trigger', 'resource_conflict_pressure'],
                'pressure_outputs' => ['cultural_mutation', 'resilience'],
                'incompatible_with' => null,
                'mutation_axes' => ['adaptation_depth'],
            ],

            // ===== DOMAIN 5: MEMORY/REINTERPRETATION =====
            
            // Group A: Memory Formation
            [
                'code' => 'CANONICAL_HISTORY',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Official, authorized version of historical narrative. High strength creates stable narrative, low creates competing versions.',
                'origin_sources' => ['official_chronicles', 'state_histories'],
                'preconditions' => ['authority > 5'],
                'pressure_inputs' => ['authority', 'institutional_education'],
                'pressure_outputs' => ['narrative_stability', 'legitimacy'],
                'incompatible_with' => null,
                'mutation_axes' => ['canonicity_level'],
            ],
            [
                'code' => 'SELECTIVE_MEMORY',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Systematic forgetting or suppression of inconvenient past. Creates denial, scapegoating, and taboo zones.',
                'origin_sources' => ['historical_denial', 'suppression'],
                'preconditions' => null,
                'pressure_inputs' => ['trauma_encoding', 'political_pressure'],
                'pressure_outputs' => ['denial', 'scapegoating', 'taboo_zones'],
                'incompatible_with' => ['TRANSPARENCY_MYTH', 'TRUTH_SEEKING'],
                'mutation_axes' => ['suppression_depth'],
            ],
            [
                'code' => 'ORAL_WRITTEN_RATIO',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'Balance between oral tradition and written records. High oral creates fast myth drift, high written creates rigidity.',
                'origin_sources' => ['oral_tradition', 'written_chronicles', 'literacy'],
                'preconditions' => null,
                'pressure_inputs' => ['literacy_rate', 'institutional_education'],
                'pressure_outputs' => ['myth_drift_rate', 'narrative_flexibility'],
                'incompatible_with' => null,
                'mutation_axes' => ['oral_dominance'],
            ],

            // Group B: Memory Distortion
            [
                'code' => 'MYTH_REINTERPRETATION_PRESSURE',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Force pushing historical events toward mythic/sacred interpretation. Creates sacred timelines.',
                'origin_sources' => ['sacralization', 'mythologization'],
                'preconditions' => ['symbolic_dominance > 6'],
                'pressure_inputs' => ['time_distance', 'ritual_pressure'],
                'pressure_outputs' => ['sacralization', 'historical_distance'],
                'incompatible_with' => null,
                'mutation_axes' => ['mythologization_rate'],
            ],
            [
                'code' => 'HISTORICAL_REVISIONISM',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Active rewriting of history for present political needs. Reforges legitimacy but creates narrative conflict.',
                'origin_sources' => ['propaganda', 'rewritten_chronicles'],
                'preconditions' => null,
                'pressure_inputs' => ['political_pressure', 'legitimacy_crisis'],
                'pressure_outputs' => ['legitimacy_reforge', 'narrative_conflict'],
                'incompatible_with' => null,
                'mutation_axes' => ['revision_depth'],
            ],
            [
                'code' => 'TRAUMA_ENCODING',
                'ontology' => 'symbolic',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'How collective trauma becomes embedded in cultural memory. Creates taboos, fear loops, and silence zones.',
                'origin_sources' => ['collective_trauma', 'catastrophe_memory'],
                'preconditions' => ['collapse OR famine'],
                'pressure_inputs' => ['famine_trigger', 'collapse_events'],
                'pressure_outputs' => ['cultural_taboo', 'fear_loop', 'avoidance'],
                'incompatible_with' => null,
                'mutation_axes' => ['trauma_depth'],
            ],

            // Group C: Memory Transmission
            [
                'code' => 'RITUALIZED_REMEMBRANCE',
                'ontology' => 'behavioral',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Regular ritual reenactment of historical events. Keeps past active and reinforces identity.',
                'origin_sources' => ['commemorations', 'reenactments', 'festivals'],
                'preconditions' => null,
                'pressure_inputs' => ['ritual_strength', 'canonical_history'],
                'pressure_outputs' => ['past_activation', 'identity_reinforcement'],
                'incompatible_with' => null,
                'mutation_axes' => ['ritual_frequency'],
            ],
            [
                'code' => 'INSTITUTIONAL_EDUCATION',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Systematic teaching of official history. Creates narrative consistency across generations.',
                'origin_sources' => ['schools', 'academies', 'apprenticeships'],
                'preconditions' => null,
                'pressure_inputs' => ['state_power', 'literacy'],
                'pressure_outputs' => ['narrative_consistency', 'generational_continuity'],
                'incompatible_with' => ['FRAGMENTED_POWER', 'DECENTRALIZATION'],
                'mutation_axes' => ['education_reach'],
            ],
            [
                'code' => 'ARTIFACT_ANCHORING',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Physical objects that anchor and verify historical memory. Loss of artifacts leads to mythologization.',
                'origin_sources' => ['relics', 'monuments', 'archives'],
                'preconditions' => null,
                'pressure_inputs' => ['material_culture', 'preservation'],
                'pressure_outputs' => ['memory_durability', 'relic_power'],
                'incompatible_with' => null,
                'mutation_axes' => ['artifact_density'],
            ],

            // Group D: Memory Feedback
            [
                'code' => 'GRIEVANCE_ACCUMULATION',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Buildup of historical wrongs into active conflict motivation. Creates revenge cycles and eternal enemy narratives.',
                'origin_sources' => ['historical_injustice', 'unresolved_conflicts'],
                'preconditions' => null,
                'pressure_inputs' => ['inequality_gradient', 'selective_memory'],
                'pressure_outputs' => ['conflict_ignition', 'revenge_cycles'],
                'incompatible_with' => null,
                'mutation_axes' => ['grievance_depth'],
            ],
            [
                'code' => 'NOSTALGIA_PRESSURE',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Idealization of past leading to regressive policies. Creates resistance to change and lost golden age myths.',
                'origin_sources' => ['golden_age_myth', 'decline_narrative'],
                'preconditions' => null,
                'pressure_inputs' => ['crisis', 'decline_perception'],
                'pressure_outputs' => ['regression_policy', 'change_resistance'],
                'incompatible_with' => ['PROGRESSIVE_MYTH', 'INNOVATION_CULT'],
                'mutation_axes' => ['nostalgia_intensity'],
            ],
            [
                'code' => 'IDENTITY_FOSSILIZATION',
                'ontology' => 'symbolic',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Rigid crystallization of identity around historical memory. Very slow decay, creates cultural rigidity.',
                'origin_sources' => ['ethnic_memory', 'caste_systems'],
                'preconditions' => null,
                'pressure_inputs' => ['trauma_encoding', 'canonical_history'],
                'pressure_outputs' => ['change_resistance', 'cultural_rigidity'],
                'incompatible_with' => null,
                'mutation_axes' => ['fossilization_depth'],
            ],
        ];
    }
}
