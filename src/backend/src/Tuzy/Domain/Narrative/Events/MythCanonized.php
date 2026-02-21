<?php

namespace Tuzy\Domain\Narrative\Events;

use App\Models\UniverseModel;
use App\Models\WorldMyth;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MythCanonized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorldMyth $myth,
        public readonly UniverseModel $universe
    ) {}
}
