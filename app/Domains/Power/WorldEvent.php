<?php

namespace App\Domains\Power;

use App\Domains\Power\Enums\PowerStage;

class WorldEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,      // e.g., 'seal_crack', 'spirit_surge'
        public readonly float $magnitude,  // 0.0 to 1.0
        public readonly float $permanence, // 0.0 to 1.0
        public readonly string $visibility,// 'secret', 'rumor', 'public'
        public readonly int $epoch
    ) {}
}
