<?php

namespace App\Domains\Social\Enums;

enum AddressingScope: string
{
    case PUBLIC = 'public';   // In front of others, formal
    case PRIVATE = 'private'; // Alone, honest/intimate/hostile
}
