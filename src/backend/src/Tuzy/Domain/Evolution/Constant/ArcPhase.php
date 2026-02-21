<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Constant;

/**
 * Macro arc phase derived from state (tension/entropy/innovation). Used for preset transition.
 */
enum ArcPhase: string
{
    case GENESIS = 'genesis';
    case EXPANSION = 'expansion';
    case GOLDEN_AGE = 'golden_age';
    case STAGNATION = 'stagnation';
    case CRISIS = 'crisis';
    case COLLAPSE = 'collapse';
    case REFORMATION = 'reformation';
}


