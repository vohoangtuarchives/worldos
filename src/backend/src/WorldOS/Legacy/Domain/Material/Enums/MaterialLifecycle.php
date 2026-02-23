<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Material\Enums;

enum MaterialLifecycle: string
{
    case DORMANT = 'dormant';
    case ACTIVE = 'active';
    case DECAYING = 'decaying';
    case LEGACY = 'legacy';
}
