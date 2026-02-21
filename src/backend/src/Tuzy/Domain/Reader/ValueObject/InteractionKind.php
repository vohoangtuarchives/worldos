<?php

declare(strict_types=1);

namespace Tuzy\Domain\Reader\ValueObject;

/**
 * Kind of reader interaction (vote, choice, reaction).
 * Domain-only enum.
 */
enum InteractionKind: string
{
    case VOTE = 'vote';
    case CHOICE = 'choice';
    case REACTION = 'reaction';
}
