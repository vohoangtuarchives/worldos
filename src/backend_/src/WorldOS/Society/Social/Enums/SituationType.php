<?php

declare(strict_types=1);

namespace WorldOS\Society\Social\Enums;

enum SituationType: string
{
    case DIALOGUE = 'dialogue';
    case COMBAT = 'combat';
    case RITUAL = 'ritual';
    case DESPERATE = 'desperate';
}
