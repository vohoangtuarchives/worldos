<?php

declare(strict_types=1);

namespace Tuzy\Domain\Social\ValueObject;

/**
 * Addressing scope: public (formal) vs private (intimate).
 * Domain-only enum.
 */
enum AddressingScope: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
}
