<?php

return [
    /*
    |--------------------------------------------------------------------------
    | World Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the autonomous world engine, including entropy settings,
    | shock event probabilities, and autonomous behavior parameters.
    |
    */

    'autonomous' => [
        'enabled' => env('WORLD_AUTONOMOUS', false),
        'tick_interval' => env('WORLD_TICK_INTERVAL', 300), // seconds
        'max_entropy' => 1.0,
        'entropy_reset_threshold' => 0.95,
    ],

    'entropy' => [
        'base_increment' => 0.02,
        'high_entropy_multiplier' => 2.0,
        'collapse_threshold' => 0.9,
        'critical_threshold' => 0.7,
        'stable_threshold' => 0.3,
    ],

    'shock_event_weights' => [
        'plague' => 0.15,
        'civil_war' => 0.20,
        'magic_collapse' => 0.10,
        'famine' => 0.15,
        'invasion' => 0.15,
        'earthquake' => 0.10,
        'myth_awakening' => 0.15,
    ],

    'character_survival' => [
        'base_survival_rate' => 0.8,
        'survival_threshold' => 0.3,
        'death_probability_factor' => 0.7,
        'plot_armor_decay_rate' => 0.01,
        'main_character_protection' => 0.4,
    ],

    'narrative' => [
        'story_threshold' => 5,
        'civilization_threshold' => 4,
        'peace_duration_threshold' => 200,
        'entropy_trigger' => 70,
        'exhaustion_threshold' => 0.25,
    ],

    'civilization' => [
        'max_cycles' => 10,
        'collapse_probability_base' => 0.1,
        'recovery_time_base' => 50, // years
        'golden_age_probability' => 0.05,
    ],

    'regions' => [
        'default_regions' => [
            'north_capital',
            'south_plains',
            'east_mountains',
            'west_coast',
            'central_desert',
            'mystic_forest',
        ],
        'max_regions' => 20,
        'region_instability_threshold' => 0.6,
    ],

    'factions' => [
        'max_factions' => 15,
        'instability_threshold' => 0.7,
        'war_probability_threshold' => 0.8,
        'alliance_probability' => 0.3,
    ],

    'resources' => [
        'scarcity_threshold' => 0.7,
        'abundance_threshold' => 0.3,
        'depletion_rate' => 0.01,
        'regeneration_rate' => 0.005,
    ],

    'myth' => [
        'stability_threshold' => 0.6,
        'collapse_probability' => 0.15,
        'awakening_probability' => 0.05,
        'corruption_rate' => 0.02,
    ],

    'performance' => [
        'batch_size' => 100,
        'cache_ttl' => 3600, // seconds
        'max_concurrent_ticks' => 5,
        'enable_metrics' => true,
    ],

    'debug' => [
        'enable_logging' => env('WORLD_DEBUG_LOGGING', false),
        'log_level' => env('WORLD_LOG_LEVEL', 'info'),
        'save_snapshots' => env('WORLD_SAVE_SNAPSHOTS', false),
        'snapshot_interval' => 10, // ticks
    ],
];
