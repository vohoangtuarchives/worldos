<?php

namespace WorldOS\Legacy\Application\Vietnamese\Config;

class EventDimensionMapping
{
    /**
     * Event type → Dimension contribution weights
     */
    public const MAP = [
        'battle' => [
            'military' => 1.0,
            'territory' => 0.3,
            'rebellion' => 0.4,
        ],

        'founding_state' => [
            'governance' => 1.0,
            'territory' => 0.6,
            'military' => 0.5,
        ],

        'reform' => [
            'reform' => 1.0,
            'governance' => 0.6,
            'education' => 0.4,
        ],

        'writing_book' => [
            'culture' => 0.8,
            'philosophy' => 0.6,
            'education' => 0.5,
        ],

        'religion_founding' => [
            'spirituality' => 1.0,
            'culture' => 0.5,
            'philosophy' => 0.6,
        ],

        'territorial_expansion' => [
            'territory' => 1.0,
            'military' => 0.6,
            'governance' => 0.3,
        ],

        'rebellion' => [
            'rebellion' => 1.0,
            'military' => 0.5,
            'governance' => 0.3,
        ],

        'diplomacy' => [
            'diplomacy' => 1.0,
            'governance' => 0.4,
            'reform' => 0.2,
        ],

        'myth_event' => [
            'mythic' => 1.0,
            'spirituality' => 0.6,
            'culture' => 0.4,
        ],

        'legal_reform' => [
            'governance' => 0.9,
            'reform' => 0.8,
            'education' => 0.4,
        ],

        'education_system' => [
            'education' => 1.0,
            'governance' => 0.5,
            'culture' => 0.3,
        ],

        'economic_policy' => [
            'economic' => 1.0,
            'governance' => 0.6,
            'reform' => 0.4,
        ],
    ];

    /**
     * Scale → Multiplier factor
     * 1 = village/local, 5 = civilizational
     */
    public const SCALE_FACTORS = [
        1 => 0.2,
        2 => 0.4,
        3 => 0.6,
        4 => 0.8,
        5 => 1.0,
    ];

    /**
     * All 12 dimensions (for initialization)
     */
    public const DIMENSIONS = [
        'military',
        'governance',
        'territory',
        'philosophy',
        'education',
        'culture',
        'spirituality',
        'rebellion',
        'reform',
        'diplomacy',
        'economic',
        'mythic',
    ];
}
