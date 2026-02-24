<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Exception;

use RuntimeException;

final class WorldNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("World not found: {$id}", 0, null);
    }
}
