<?php

declare(strict_types=1);

namespace App\Modules\Universe\Events;

use App\Modules\Universe\ValueObjects\UniverseId;

/**
 * Domain Event: Universe has collapsed — terminal state.
 */
final readonly class UniverseCollapsed
{
    public function __construct(
        public UniverseId $universeId,
        public string $cause,
        public int $finalTick,
    ) {
    }
}
