<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

enum WorldStatus: string
{
    case DRAFT = 'draft';
    case SEALED = 'sealed';
    case ARCHIVED = 'archived';

    public function isSealed(): bool
    {
        return $this === self::SEALED;
    }
}
