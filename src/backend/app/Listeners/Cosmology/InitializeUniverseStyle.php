<?php

namespace App\Listeners\Cosmology;

use App\Domains\World\Events\WorldDefined;
use App\Domains\Genre\GenreRegistry;
use App\Models\World;
use App\Models\UniverseStyle;
use Illuminate\Support\Facades\Log;

class InitializeUniverseStyle
{
    public function __construct(
        private GenreRegistry $genreRegistry
    ) {}

    public function handle(WorldDefined $event): void
    {
        $world = World::find($event->worldId);
        if (!$world) {
            // If model doesn't exist yet (e.g. event fired before commit), we might need to retry or find by other means
            Log::warning("InitializeUniverseStyle: World not found", ['world_id' => $event->worldId]);
            return;
        }

        $genreKey = $world->genre ?? 'xianxia';
        $genreDef = $this->genreRegistry->get($genreKey);

        if (!$genreDef) {
            Log::warning("InitializeUniverseStyle: Genre definition not found", ['genre' => $genreKey]);
            return;
        }

        $bias = $genreDef->getPhysicsBias();

        UniverseStyle::create([
            'world_id' => $world->id,
            'name' => "{$genreDef->displayName()} Style",
            'style_vector' => $bias,
            'version' => 1,
            'is_active' => true,
        ]);

        Log::info("UniverseStyle initialized for world", [
            'world_id' => $world->id,
            'genre' => $genreKey,
        ]);
    }
}
