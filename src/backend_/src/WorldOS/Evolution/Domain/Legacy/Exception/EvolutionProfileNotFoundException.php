<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Exception;

use RuntimeException;

final class EvolutionProfileNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("EvolutionProfile not found: {$id}", 0, null);
    }
}
