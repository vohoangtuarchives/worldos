<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Saga\Services\GenesisPresetService;
use App\Domains\Saga\Services\SagaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writer API: Genesis (create Saga from preset).
 */
class WriterGenesisController extends Controller
{
    public function __construct(
        private GenesisPresetService $presetService,
        private SagaService $sagaService
    ) {}

    /**
     * GET /api/writer/genesis/presets — categories and presets for Genesis form.
     */
    public function presets(): JsonResponse
    {
        $categories = $this->presetService->allByCategory();
        return response()->json(['categories' => $categories]);
    }

    /**
     * POST /api/writer/genesis — create Saga from preset/custom config (same as Blade storeGenesis).
     */
    /**
     * POST /api/writer/genesis/world — Step 1: Create World Container (No preset).
     */
    public function storeWorld(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genre' => 'nullable|string',
            'origin_type' => 'nullable|string',
            // Physics/Meta configs can be added here
        ]);

        $world = $this->sagaService->createWorldContainer($validated['name'], [
            'genre' => $validated['genre'] ?? 'historical',
            'origin_type' => $validated['origin_type'] ?? 'cosmic',
        ]);

        return response()->json([
            'world_id' => $world->id,
            'name' => $world->name,
            'message' => 'World Container initialized. Ready for Universe Seeding.',
        ], 201);
    }

    /**
     * POST /api/writer/genesis/universe — Step 2: Spawn Universe from Preset.
     */
    public function storeUniverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'world_id' => 'required|exists:worlds,id',
            'preset_key' => 'required|string',
        ]);

        $world = \App\Models\World::find($validated['world_id']);
        $universe = $this->sagaService->spawnUniverseFromPreset($world, $validated['preset_key']);

        return response()->json([
            'universe_id' => $universe->getId(),
            'name' => $universe->getName(),
            'message' => "Universe spawned using preset: {$validated['preset_key']}",
        ], 201);
    }
}
