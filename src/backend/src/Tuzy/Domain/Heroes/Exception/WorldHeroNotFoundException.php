<?php

declare(strict_types=1);

namespace Tuzy\Domain\Heroes\Exception;

use RuntimeException;

final class WorldHeroNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("WorldHero not found: {$id}", 0, null);
    }
}
