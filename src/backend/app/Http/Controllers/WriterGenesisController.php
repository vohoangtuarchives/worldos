<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\WorldOS\Runtime\Actions\SpawnUniverseAction;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Dto\SpawnUniverseDTO;
use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use App\WorldOS\World\Entities\WorldEntity;
use App\WorldOS\World\ValueObjects\WorldId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer Genesis Controller — World & Universe creation endpoints.
 *
 * From docs §13.1: POST /api/writer/genesis/world, POST /api/writer/genesis/universe
 *
 * Thin controller — validates, maps to DTO, calls Action, returns response.
 */
class WriterGenesisController extends Controller
{
    /**
     * POST /api/writer/genesis/world
     *
     * Create a new World from a preset key.
     */
    public function createWorld(
        Request $request,
        WorldRepositoryInterface $worldRepository,
    ): JsonResponse {
        $request->validate([
            'preset_key' => 'required|string',
            'name' => 'nullable|string|max:255',
        ]);

        $presetKey = $request->input('preset_key');
        $name = $request->input('name', 'World_' . now()->format('Ymd_His'));

        // Load preset from config/presets
        $presetData = config("worldos.presets.{$presetKey}");

        if (!$presetData) {
            return response()->json([
                'error' => "Unknown preset: {$presetKey}",
            ], 422);
        }

        $world = WorldEntity::createFromPreset(
            presetKey: $presetKey,
            presetData: $presetData,
        );

        $worldRepository->save($world);

        return response()->json([
            'world_id' => $world->getId()->value,
            'preset_key' => $presetKey,
            'name' => $name,
            'message' => 'World created successfully',
        ], 201);
    }

    /**
     * POST /api/writer/genesis/universe
     *
     * Spawn a new Universe in an existing World.
     */
    public function createUniverse(
        Request $request,
        SpawnUniverseAction $spawnAction,
    ): JsonResponse {
        $request->validate([
            'world_id' => 'required|uuid',
            'name' => 'nullable|string|max:255',
            'parent_universe_id' => 'nullable|uuid',
        ]);

        $dto = new SpawnUniverseDTO(
            worldId: new WorldId($request->input('world_id')),
            name: $request->input('name'),
            parentUniverseId: $request->input('parent_universe_id'),
        );

        $universe = $spawnAction->handle($dto);

        return response()->json([
            'universe_id' => $universe->getId()->value,
            'world_id' => $request->input('world_id'),
            'status' => $universe->getStatus()->value,
            'message' => 'Universe spawned successfully',
        ], 201);
    }
}
