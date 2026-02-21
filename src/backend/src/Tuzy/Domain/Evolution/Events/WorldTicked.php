<?php

namespace Tuzy\Domain\Evolution\Events;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use App\Models\World;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorldTicked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public World $world,
        public WorldStateVector $state
    ) {}
}
