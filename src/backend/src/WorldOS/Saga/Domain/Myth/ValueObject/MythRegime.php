<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\ValueObject;

enum MythRegime: string
{
    case ASCENSION_ERA = 'ascension_era';
    case CORRUPTION_ERA = 'corruption_era';
    case RECURSIVE_ERA = 'recursive_era';
    case ESCAPE_ERA = 'escape_era';
    case CONVERGENCE_ERA = 'convergence_era';
    case TRANSITIONAL = 'transitional';
}
