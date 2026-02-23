<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\Exception;

use RuntimeException;

final class HeroNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Hero not found with ID: {$id}");
    }
}
