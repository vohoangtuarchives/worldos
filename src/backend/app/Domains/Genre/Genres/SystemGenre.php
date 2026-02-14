<?php

namespace App\Domains\Genre\Genres;

use App\Domains\Genre\AbstractGenre;

class SystemGenre extends AbstractGenre
{
    const KEY = 'system';
    const NAME = 'System (LitRPG)';
    const DESCRIPTION = 'A world governed by a Game System. Levels, skills, and quests define reality.';

    const TERMINOLOGY = [
        'energy' => 'Mana/MP',
        'school' => 'Guild',
        'leader' => 'Guild Master',
        'student' => 'Player',
        'elite' => 'Ranker',
        'weapon' => 'Item',
        'currency' => 'Gold/Credits',
        'text' => 'Skill Book',
        'event_conflict' => 'Guild War',
        'event_gathering' => 'Raid',
        'level_up' => 'Level Up',
    ];

    const MATERIALS = [
        'EXPERIENCE_POINTS',
        'SYSTEM_OMNIPOTENCE',
        'DUNGEON_CORE',
        'SKILL_SLOT',
    ];

    const PROMPT = "Rewrite the following historical event in the style of a LitRPG/System novel (like 'Solo Leveling'). Use terms like 'Level Up', 'Quest', 'System Notification', 'Stats'. Include blue system boxes description where appropriate.";
}
