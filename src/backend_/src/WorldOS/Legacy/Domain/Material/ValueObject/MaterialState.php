<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Material\ValueObject;

enum MaterialState: string
{
    case STABLE = 'stable';
    case BROKEN = 'broken';
    case DAMAGED = 'damaged';
    case WORN = 'worn';
    case RETIRED = 'retired';
    case UNSTABLE = 'unstable';
    case CORRUPTED = 'corrupted';
}
