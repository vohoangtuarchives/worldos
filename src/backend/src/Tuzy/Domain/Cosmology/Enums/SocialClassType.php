<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\Enums;

enum SocialClassType: string
{
    case NOBILITY = 'nobility';
    case PRIESTHOOD = 'priesthood';
    case MERCHANT = 'merchant';
    case WARRIOR = 'warrior';
    case PEASANTRY = 'peasantry';
    case INTELLECTUAL = 'intellectual';
}
