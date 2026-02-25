<?php

declare(strict_types=1);

namespace App\Modules\Narrative\ValueObjects;

/**
 * Saga lifecycle status.
 */
enum SagaStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';

    public function canAdvance(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canComplete(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canArchive(): bool
    {
        return $this === self::ACTIVE || $this === self::COMPLETED;
    }

    public function isTerminal(): bool
    {
        return $this === self::ARCHIVED;
    }
}
