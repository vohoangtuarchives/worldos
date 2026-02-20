<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Exception;

use RuntimeException;

final class UniverseNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Universe not found: {$id}", 0, null);
    }
}
