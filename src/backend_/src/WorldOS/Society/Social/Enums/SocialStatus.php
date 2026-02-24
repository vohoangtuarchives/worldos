<?php

declare(strict_types=1);

namespace WorldOS\Society\Social\Enums;

enum SocialStatus: string
{
    case COMMONER = 'commoner';
    case ELITE = 'elite';
    case AUTHORITY = 'authority';
    case SOVEREIGN = 'sovereign';
}
