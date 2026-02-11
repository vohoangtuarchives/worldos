<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Material\Material;

class TechnologyInteractionMaterialsSeeder extends Seeder
{
    /**
     * Seed Technology/Infrastructure and Interaction/Inter-World materials.
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

        $this->command->info('Technology & Interaction materials seeded successfully (24 materials).');
    }

    private function getMaterials(): array
    {
        return [
            // ===== DOMAIN 4: TECHNOLOGY/INFRASTRUCTURE =====
            
            // Group A: Core Infrastructure
            [
                'code' => 'TRANSPORTATION_NETWORK',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'active',
                'description' => 'Physical network enabling movement of goods and people. High levels enable trade and control expansion, low creates fragmentation.',
                'origin_sources' => ['roads', 'rivers', 'ports', 'bridges'],
                'preconditions' => null,
                'pressure_inputs' => ['state_power', 'surplus_distribution'],
                'pressure_outputs' => ['trade_volume', 'control_reach', 'fragmentation'],
                'incompatible_with' => null,
                'mutation_axes' => ['network_density', 'maintenance_level'],
            ],
            [
                'code' => 'PRODUCTION_INFRASTRUCTURE',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Fixed facilities for resource processing and production. Enables stable surplus but incompatible with nomadic systems.',
                'origin_sources' => ['workshops', 'mills', 'forges', 'granaries'],
                'preconditions' => null,
                'pressure_inputs' => ['subsistence_base', 'specialization_depth'],
                'pressure_outputs' => ['surplus_stability', 'settlement_pressure'],
                'incompatible_with' => ['NOMADIC_MYTH', 'WANDERING_IDENTITY'],
                'mutation_axes' => ['infrastructure_scale'],
            ],
            [
                'code' => 'ENERGY_SOURCE',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'active',
                'description' => 'Primary power source for production. Variants include human labor, animal, water, wind. Limits productivity ceiling.',
                'origin_sources' => ['human_labor', 'animal_power', 'water_mills', 'wind_power'],
                'preconditions' => null,
                'pressure_inputs' => ['technology', 'labor_organization'],
                'pressure_outputs' => ['productivity_ceiling', 'power_symbolism'],
                'incompatible_with' => null,
                'mutation_axes' => ['energy_type', 'efficiency'],
            ],

            // Group B: Knowledge & Skill
            [
                'code' => 'TECHNICAL_LITERACY',
                'ontology' => 'behavioral',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Widespread understanding of technical processes. High creates resilience, decays rapidly when institutions collapse.',
                'origin_sources' => ['apprenticeships', 'guilds', 'practical_education'],
                'preconditions' => null,
                'pressure_inputs' => ['institutional_education', 'specialization_depth'],
                'pressure_outputs' => ['resilience', 'innovation_capacity'],
                'incompatible_with' => null,
                'mutation_axes' => ['literacy_breadth'],
            ],
            [
                'code' => 'KNOWLEDGE_PRESERVATION',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Systems for maintaining technical knowledge across generations. Low leads to tech regression.',
                'origin_sources' => ['libraries', 'master_records', 'oral_transmission'],
                'preconditions' => null,
                'pressure_inputs' => ['institutional_education', 'artifact_anchoring'],
                'pressure_outputs' => ['tech_continuity', 'regression_risk'],
                'incompatible_with' => ['ANTI_INTELLECTUAL_MYTH', 'KNOWLEDGE_TABOO'],
                'mutation_axes' => ['preservation_method'],
            ],
            [
                'code' => 'INNOVATION_FRICTION',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Resistance to technological change. High creates stagnation and "ancients were better" myths.',
                'origin_sources' => ['tradition', 'guild_monopolies', 'religious_restriction'],
                'preconditions' => null,
                'pressure_inputs' => ['identity_fossilization', 'canonical_history'],
                'pressure_outputs' => ['stagnation', 'regression_pressure'],
                'incompatible_with' => null,
                'mutation_axes' => ['friction_intensity'],
            ],

            // Group C: System Dependency
            [
                'code' => 'INFRASTRUCTURE_CENTRALIZATION',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Concentration of critical infrastructure. High creates efficiency but systemic fragility.',
                'origin_sources' => ['capital_cities', 'central_granaries', 'hub_systems'],
                'preconditions' => null,
                'pressure_inputs' => ['state_power', 'transportation_network'],
                'pressure_outputs' => ['efficiency', 'systemic_fragility', 'single_point_failure'],
                'incompatible_with' => null,
                'mutation_axes' => ['centralization_level'],
            ],
            [
                'code' => 'MAINTENANCE_BURDEN',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Resource cost of maintaining infrastructure. High drains economy, incompatible with weak economies.',
                'origin_sources' => ['monument_upkeep', 'road_repair', 'irrigation_maintenance'],
                'preconditions' => null,
                'pressure_inputs' => ['production_infrastructure', 'transportation_network'],
                'pressure_outputs' => ['resource_drain', 'collapse_risk'],
                'incompatible_with' => ['WEAK_ECONOMY', 'LOW_SURPLUS'],
                'mutation_axes' => ['burden_level'],
            ],
            [
                'code' => 'TECHNOLOGICAL_LOCK_IN',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Path dependency on existing technology. High prevents adaptation, creates obsolete dominance.',
                'origin_sources' => ['invested_infrastructure', 'trained_workforce', 'supply_chains'],
                'preconditions' => null,
                'pressure_inputs' => ['production_infrastructure', 'specialization_depth'],
                'pressure_outputs' => ['path_dependency', 'adaptation_resistance'],
                'incompatible_with' => null,
                'mutation_axes' => ['lock_in_depth'],
            ],

            // Group D: Failure & Regression
            [
                'code' => 'INFRASTRUCTURE_COLLAPSE_TRIGGER',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Catastrophic infrastructure failure causing social breakdown. Marks dark ages.',
                'origin_sources' => ['system_failure', 'invasion_damage', 'maintenance_collapse'],
                'preconditions' => ['infrastructure_centralization > 7 OR maintenance_burden > 8'],
                'pressure_inputs' => ['infrastructure_centralization', 'maintenance_burden'],
                'pressure_outputs' => ['social_breakdown', 'dark_age_marker'],
                'incompatible_with' => null,
                'mutation_axes' => null,
            ],
            [
                'code' => 'SKILL_ATTRITION',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Irreversible loss of technical skills. Creates mythical craftsmanship legends.',
                'origin_sources' => ['master_death', 'guild_collapse', 'knowledge_suppression'],
                'preconditions' => ['technical_literacy < 4 OR knowledge_preservation < 3'],
                'pressure_inputs' => ['technical_literacy', 'knowledge_preservation'],
                'pressure_outputs' => ['irreversible_loss', 'mythical_skill'],
                'incompatible_with' => null,
                'mutation_axes' => ['attrition_rate'],
            ],
            [
                'code' => 'TECH_MYTHOLOGIZATION',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Transformation of lost technology into sacred relics and divine craftsmanship myths.',
                'origin_sources' => ['lost_techniques', 'ancient_artifacts', 'ruin_worship'],
                'preconditions' => ['skill_attrition OR infrastructure_collapse_trigger'],
                'pressure_inputs' => ['skill_attrition', 'time_distance'],
                'pressure_outputs' => ['relic_worship', 'divine_craft_myth'],
                'incompatible_with' => null,
                'mutation_axes' => ['mythologization_depth'],
            ],

            // ===== DOMAIN 6: INTERACTION/INTER-WORLD =====
            
            // Group A: Movement
            [
                'code' => 'MIGRATION_PRESSURE',
                'ontology' => 'behavioral',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Force driving population movement between worlds. Creates cultural mixing and diaspora narratives.',
                'origin_sources' => ['famine_flight', 'conquest_displacement', 'opportunity_seeking'],
                'preconditions' => null,
                'pressure_inputs' => ['famine_trigger', 'resource_conflict_pressure', 'population'],
                'pressure_outputs' => ['cultural_mixing', 'identity_stress'],
                'incompatible_with' => ['CLOSED_IDENTITY', 'PURITY_MYTH'],
                'mutation_axes' => ['migration_intensity'],
            ],
            [
                'code' => 'TRADE_ROUTE_EXPOSURE',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Degree of connection to inter-world trade networks. Enables tech and belief diffusion, loss causes regression.',
                'origin_sources' => ['merchant_networks', 'caravan_routes', 'maritime_trade'],
                'preconditions' => null,
                'pressure_inputs' => ['transportation_network', 'surplus_distribution'],
                'pressure_outputs' => ['tech_diffusion', 'belief_diffusion', 'wealth'],
                'incompatible_with' => null,
                'mutation_axes' => ['exposure_level'],
            ],
            [
                'code' => 'KNOWLEDGE_DIFFUSION_RATE',
                'ontology' => 'behavioral',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Speed of inter-world knowledge transfer. High enables leapfrogging, creates borrowed greatness myths.',
                'origin_sources' => ['scholar_exchange', 'captured_experts', 'translated_texts'],
                'preconditions' => null,
                'pressure_inputs' => ['trade_route_exposure', 'knowledge_preservation'],
                'pressure_outputs' => ['leapfrog_potential', 'dependency'],
                'incompatible_with' => null,
                'mutation_axes' => ['diffusion_speed'],
            ],

            // Group B: Contact & Conflict
            [
                'code' => 'CULTURAL_FRICTION',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Tension from inter-world cultural contact. High hardens identity and creates "us vs them" myths.',
                'origin_sources' => ['cultural_clash', 'value_conflict', 'religious_difference'],
                'preconditions' => null,
                'pressure_inputs' => ['migration_pressure', 'trade_route_exposure'],
                'pressure_outputs' => ['identity_hardening', 'us_them_myth'],
                'incompatible_with' => null,
                'mutation_axes' => ['friction_intensity'],
            ],
            [
                'code' => 'EXTERNAL_THREAT_PRESSURE',
                'ontology' => 'behavioral',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Pressure from external military threats. Drives centralization and creates siege mentality.',
                'origin_sources' => ['invasion_threat', 'raiding', 'border_conflict'],
                'preconditions' => null,
                'pressure_inputs' => ['military_capacity', 'resource_conflict_pressure'],
                'pressure_outputs' => ['centralization', 'siege_mentality', 'militarization'],
                'incompatible_with' => null,
                'mutation_axes' => ['threat_level'],
            ],
            [
                'code' => 'INVASION_CAPABILITY',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Ability to project force into other worlds. Requires surplus economy, creates conquest or collapse.',
                'origin_sources' => ['military_strength', 'logistics', 'expansionism'],
                'preconditions' => ['surplus_distribution > 6'],
                'pressure_inputs' => ['surplus_distribution', 'transportation_network'],
                'pressure_outputs' => ['expansion', 'collapse_risk', 'conquest_memory'],
                'incompatible_with' => null,
                'mutation_axes' => ['capability_level'],
            ],

            // Group C: Influence & Dependency
            [
                'code' => 'CULTURAL_DOMINANCE',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Degree of cultural influence over other worlds. High drives assimilation and identity erasure.',
                'origin_sources' => ['prestige', 'conquest', 'economic_power'],
                'preconditions' => null,
                'pressure_inputs' => ['trade_route_exposure', 'invasion_capability'],
                'pressure_outputs' => ['assimilation', 'identity_erasure'],
                'incompatible_with' => null,
                'mutation_axes' => ['dominance_level'],
            ],
            [
                'code' => 'ECONOMIC_DEPENDENCY',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Reliance on external trade for survival. High causes sovereignty loss, creates humiliation myths when broken.',
                'origin_sources' => ['trade_dependency', 'tribute_systems', 'resource_extraction'],
                'preconditions' => null,
                'pressure_inputs' => ['trade_route_exposure', 'subsistence_base'],
                'pressure_outputs' => ['sovereignty_loss', 'vulnerability'],
                'incompatible_with' => null,
                'mutation_axes' => ['dependency_depth'],
            ],
            [
                'code' => 'POLITICAL_ENTANGLEMENT',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Involvement in inter-world political conflicts. Creates proxy wars and inherited enemies.',
                'origin_sources' => ['alliances', 'vassalage', 'treaty_obligations'],
                'preconditions' => null,
                'pressure_inputs' => ['external_threat_pressure', 'economic_dependency'],
                'pressure_outputs' => ['proxy_conflict', 'inherited_enemies'],
                'incompatible_with' => null,
                'mutation_axes' => ['entanglement_depth'],
            ],

            // Group D: Cross-World Memory
            [
                'code' => 'SHARED_TRAUMA',
                'ontology' => 'symbolic',
                'function' => 'destructive',
                'default_lifecycle' => 'dormant',
                'description' => 'Collective memory of inter-world catastrophe. Creates long-term hostility and eternal war narratives.',
                'origin_sources' => ['genocide', 'conquest', 'betrayal'],
                'preconditions' => ['invasion_capability OR external_threat_pressure'],
                'pressure_inputs' => ['trauma_encoding', 'grievance_accumulation'],
                'pressure_outputs' => ['eternal_hostility', 'war_narrative'],
                'incompatible_with' => null,
                'mutation_axes' => ['trauma_depth'],
            ],
            [
                'code' => 'COMPARATIVE_IDENTITY',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'default_lifecycle' => 'dormant',
                'description' => 'Identity defined in opposition to other worlds. "We are not them" creates mirror myths.',
                'origin_sources' => ['cultural_contrast', 'rivalry', 'differentiation'],
                'preconditions' => null,
                'pressure_inputs' => ['cultural_friction', 'identity_fossilization'],
                'pressure_outputs' => ['negative_identity', 'mirror_myth'],
                'incompatible_with' => null,
                'mutation_axes' => ['contrast_intensity'],
            ],
            [
                'code' => 'WORLD_REPUTATION',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'default_lifecycle' => 'dormant',
                'description' => 'Inter-world perception and status. Affects diplomacy and creates legendary land myths.',
                'origin_sources' => ['achievements', 'power', 'cultural_output'],
                'preconditions' => null,
                'pressure_inputs' => ['cultural_dominance', 'trade_route_exposure'],
                'pressure_outputs' => ['diplomatic_leverage', 'legendary_status'],
                'incompatible_with' => null,
                'mutation_axes' => ['reputation_level'],
            ],
        ];
    }
}
