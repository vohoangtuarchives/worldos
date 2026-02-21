<?php

declare(strict_types=1);

namespace Tuzy\Domain\Social\Enums;

enum RelativeAgeRank: string
{
    case YOUTH = 'youth';
    case JUNIOR = 'junior';
    case MATURE = 'mature';
    case SENIOR = 'senior';
    case ANCIENT = 'ancient';
}
