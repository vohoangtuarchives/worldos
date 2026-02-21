<?php

namespace Tuzy\Application\Genre\Genres;

use Tuzy\Domain\Genre\AbstractGenre;

class XianxiaGenre extends AbstractGenre
{
    const KEY = 'xianxia';
    const NAME = 'Xianxia (Cultivation)';
    const DESCRIPTION = 'A world of cultivation, immortals, spirit beasts, and the Dao. Mortals are but ants.';

    const TERMINOLOGY = [
        'energy' => 'Spirit Qi',
        'school' => 'Sect',
        'leader' => 'Patriarch',
        'student' => 'Daoist',
        'elite' => 'Immortal Elder',
        'weapon' => 'Flying Sword/Artifact',
        'currency' => 'Spirit Stones',
        'text' => 'Jade Slip',
        'event_conflict' => 'Realm War',
        'event_gathering' => 'Dao Discussion',
        'tribulation' => 'Heavenly Tribulation',
    ];

    const MATERIALS = [
        'SPIRIT_QI',
        'DEMONIC_ENERGY',
        'ALCHEMY_PILL',
        'SPIRIT_VEIN',
    ];

    const PROMPT = "Rewrite the following historical event in the style of a Xianxia/Cultivation novel (like 'Renegade Immortal'). Use terms like 'Qi', 'Dao', 'Tribulation', 'Realms'. Emphasize the scale of power, the ruthlessness of the cultivation world, and the insignificance of mortals.";
}
