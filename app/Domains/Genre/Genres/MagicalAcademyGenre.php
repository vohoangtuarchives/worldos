<?php

namespace App\Domains\Genre\Genres;

use App\Domains\Genre\AbstractGenre;

class MagicalAcademyGenre extends AbstractGenre
{
    const KEY = 'academy';
    const NAME = 'Magical Academy (Western Fantasy)';
    const DESCRIPTION = 'A world of wands, spells, potions, and valid houses. School life meets dark mysteries.';

    const TERMINOLOGY = [
        'energy' => 'Mana',
        'school' => 'Academy',
        'leader' => 'Headmaster',
        'student' => 'Student',
        'elite' => 'Professor',
        'weapon' => 'Wand/Staff',
        'currency' => 'Coins',
        'text' => 'Grimoire',
        'event_conflict' => 'Duel',
        'event_gathering' => 'Assembly/Feast',
        'house' => 'House',
    ];

    const MATERIALS = [
        'MANA_LEYLINES',
        'ANCIENT_SPELLS',
        'BLOODLINE_PURITY',
        'DARK_ARTS',
    ];

    const PROMPT = "Rewrite the following historical event in the style of a Magical Academy novel (like 'Harry Potter'). Use terms like 'Spells', 'Potions', 'House Points', 'Wands'. Focus on the school setting, student rivalries, and hidden magical mysteries.";
}
