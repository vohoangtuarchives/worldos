<?php

namespace App\Domains\Genre\Genres;

use App\Domains\Genre\AbstractGenre;

class WuxiaGenre extends AbstractGenre
{
    const KEY = 'wuxia';
    const NAME = 'Wuxia (Martial Arts)';
    const DESCRIPTION = 'A world of martial arts, sects, honor, and revenge. The Jianghu is a dangerous place.';

    const TERMINOLOGY = [
        'energy' => 'Internal Energy (Qi)',
        'school' => 'Sect',
        'leader' => 'Sect Leader',
        'student' => 'Disciple',
        'elite' => 'Elder',
        'weapon' => 'Sword/Blade',
        'currency' => 'Silver Tael',
        'text' => 'Secret Manual',
        'event_conflict' => 'Sect War',
        'event_gathering' => 'Martial Arts Tournament',
    ];

    const MATERIALS = [
        'INTERNAL_ENERGY',
        'MARTIAL_TEXT',
        'SECT_REPUTATION',
        'LEGENDARY_WEAPON',
    ];

    const PROMPT = "Rewrite the following historical event in the style of a classic Wuxia novel (like Jin Yong). Use terms like 'Jianghu', 'Sect', 'Internal Energy'. Focus on martial honor, revenge, and the struggle for power between factions.";
}
