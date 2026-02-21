<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\Exception;

use RuntimeException;

final class UniverseStyleNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("UniverseStyle not found: {$id}", 0, null);
    }
}
