<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Events;

use App\WorldOS\CivilizationMemory\ValueObjects\ScarId;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Domain Event: A new Scar has been created.
 */
final readonly class ScarCreated
{
    public function __construct(
        public ScarId $scarId,
        public UniverseId $universeId,
        public string $type,
        public int $weight,
        public int $tick,
    ) {
    }
}
