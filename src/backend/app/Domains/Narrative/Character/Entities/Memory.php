<?php

namespace App\Domains\Narrative\Character\Entities;

use Tuzy\Domain\Narrative\ValueObject\EmotionState;
use Illuminate\Support\Collection;

class Memory
{
    public function __construct(
        public readonly string $id,
        public readonly string $type, // 'semantic', 'episodic'
        public readonly string $content,
        public readonly string $visibility, // 'public', 'private', 'secret'
        public readonly float $confidence,
        public readonly ?string $timelineNodeId = null
    ) {}
}
