<?php

namespace WorldOS\Evolution\Domain\Legacy\Events;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
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
