<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Legacy\Exception;

use RuntimeException;

final class SagaNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Saga not found: {$id}", 0, null);
    }
}
