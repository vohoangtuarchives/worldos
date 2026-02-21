<?php

declare(strict_types=1);

namespace Tuzy\Domain\Social\Enums;

enum SocialStatus: string
{
    case COMMONER = 'commoner';
    case ELITE = 'elite';
    case AUTHORITY = 'authority';
    case SOVEREIGN = 'sovereign';
}
