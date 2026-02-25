<?php

declare(strict_types=1);

namespace App\Modules\Universe\Dto;

use App\WorldOS\Cosmology\ValueObjects\CascadeThresholds;

/**
 * DTO for ticking a Universe one step.
 */
final readonly class TickUniverseDTO
{
    public function __construct(
        public string $universeId,
        public ?CascadeThresholds $cascadeThresholds = null,
    ) {
    }
}
