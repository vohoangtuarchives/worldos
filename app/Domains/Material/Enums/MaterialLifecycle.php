<?php

namespace App\Domains\Material\Enums;

enum MaterialLifecycle: string
{
    case DORMANT = 'dormant';
    case ACTIVE = 'active';
    case DECAYING = 'decaying';
    case LEGACY = 'legacy';
}
