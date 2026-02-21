<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\Exception;

use RuntimeException;

final class NarrativeSeriesNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("NarrativeSeries not found: {$id}", 0, null);
    }
}
