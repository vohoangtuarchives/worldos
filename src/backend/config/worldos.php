<?php

/**
 * WorldOS Configuration — presets and defaults.
 *
 * Each preset defines a complete set of LawVector dimensions (θ₁-θ₁₇)
 * that represent a world's "physics" and cultural tendencies.
 */
return [
    'presets' => [
        'xianxia' => [
            'name' => 'Tu Tiên / Xianxia',
            'genre' => 'xianxia',
            'law_vector' => [
                'dimensionality' => 0.7,        // θ₁  Multi-layered realms
                'causality_rigidity' => 0.4,     // θ₂  Karma & fate flexible
                'energy_stability' => 0.6,       // θ₃  Qi cycles
                'interaction_strength' => 0.8,   // θ₄  Strong spiritual forces
                'entropy_growth' => 0.3,         // θ₅  Slow natural disorder
                'matter_complexity' => 0.7,      // θ₆  Complex materials (herbs, ores)
                'self_organization' => 0.8,      // θ₇  Sects, hierarchies
                'stability_basin_depth' => 0.6,  // θ₈  Moderate stability
                'collapse_probability' => 0.3,   // θ₉  Rare collapses
                'abiogenesis' => 0.5,            // θ₁₀ Spirit beasts
                'mutation_volatility' => 0.6,    // θ₁₁ Cultivation breakthroughs
                'adaptation_efficiency' => 0.7,  // θ₁₂ Strong selection
                'cognitive_ceiling' => 0.9,      // θ₁₃ Celestial consciousness
                'myth_formation' => 0.9,         // θ₁₄ Dao, Tribulation lore
                'memory_persistence' => 0.8,     // θ₁₅ Ancient sect records
                'tech_accumulation_rate' => 0.2, // θ₁₆ Low tech
                'meta_system_awareness' => 0.7,  // θ₁₇ Dao comprehension
            ],
        ],

        'cyberpunk' => [
            'name' => 'Cyberpunk',
            'genre' => 'cyberpunk',
            'law_vector' => [
                'dimensionality' => 0.4,
                'causality_rigidity' => 0.8,
                'energy_stability' => 0.5,
                'interaction_strength' => 0.3,
                'entropy_growth' => 0.7,
                'matter_complexity' => 0.8,
                'self_organization' => 0.3,
                'stability_basin_depth' => 0.3,
                'collapse_probability' => 0.6,
                'abiogenesis' => 0.2,
                'mutation_volatility' => 0.4,
                'adaptation_efficiency' => 0.5,
                'cognitive_ceiling' => 0.8,
                'myth_formation' => 0.2,
                'memory_persistence' => 0.4,
                'tech_accumulation_rate' => 0.95,
                'meta_system_awareness' => 0.6,
            ],
        ],

        'fantasy' => [
            'name' => 'High Fantasy',
            'genre' => 'fantasy',
            'law_vector' => [
                'dimensionality' => 0.6,
                'causality_rigidity' => 0.5,
                'energy_stability' => 0.6,
                'interaction_strength' => 0.6,
                'entropy_growth' => 0.4,
                'matter_complexity' => 0.6,
                'self_organization' => 0.6,
                'stability_basin_depth' => 0.5,
                'collapse_probability' => 0.3,
                'abiogenesis' => 0.5,
                'mutation_volatility' => 0.5,
                'adaptation_efficiency' => 0.6,
                'cognitive_ceiling' => 0.7,
                'myth_formation' => 0.7,
                'memory_persistence' => 0.6,
                'tech_accumulation_rate' => 0.3,
                'meta_system_awareness' => 0.5,
            ],
        ],

        'historical' => [
            'name' => 'Historical Realism',
            'genre' => 'historical',
            'law_vector' => [
                'dimensionality' => 0.3,
                'causality_rigidity' => 0.9,
                'energy_stability' => 0.7,
                'interaction_strength' => 0.4,
                'entropy_growth' => 0.5,
                'matter_complexity' => 0.5,
                'self_organization' => 0.5,
                'stability_basin_depth' => 0.6,
                'collapse_probability' => 0.4,
                'abiogenesis' => 0.3,
                'mutation_volatility' => 0.3,
                'adaptation_efficiency' => 0.5,
                'cognitive_ceiling' => 0.6,
                'myth_formation' => 0.4,
                'memory_persistence' => 0.7,
                'tech_accumulation_rate' => 0.5,
                'meta_system_awareness' => 0.3,
            ],
        ],

        'postapocalyptic' => [
            'name' => 'Post-Apocalyptic',
            'genre' => 'postapocalyptic',
            'law_vector' => [
                'dimensionality' => 0.3,
                'causality_rigidity' => 0.7,
                'energy_stability' => 0.3,
                'interaction_strength' => 0.5,
                'entropy_growth' => 0.8,
                'matter_complexity' => 0.4,
                'self_organization' => 0.2,
                'stability_basin_depth' => 0.2,
                'collapse_probability' => 0.7,
                'abiogenesis' => 0.3,
                'mutation_volatility' => 0.7,
                'adaptation_efficiency' => 0.8,
                'cognitive_ceiling' => 0.4,
                'myth_formation' => 0.5,
                'memory_persistence' => 0.3,
                'tech_accumulation_rate' => 0.2,
                'meta_system_awareness' => 0.2,
            ],
        ],
    ],
];
