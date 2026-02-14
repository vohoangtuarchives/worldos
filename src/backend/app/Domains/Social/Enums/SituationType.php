<?php

namespace App\Domains\Social\Enums;

enum SituationType: string
{
    case DIALOGUE = 'dialogue';     // Normal conversation
    case COMBAT = 'combat';         // Fighting
    case RITUAL = 'ritual';         // Formal ceremony
    case DESPERATE = 'desperate';   // Life or death situation
}
