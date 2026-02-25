<?php

declare(strict_types=1);

namespace App\Modules\Universe\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Universe\Actions\SpawnUniverseAction;
use App\Modules\Universe\Actions\TickUniverseAction;
use App\Modules\Universe\Dto\SpawnUniverseDTO;
use App\Modules\Universe\Dto\TickUniverseDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class UniverseController extends Controller
{
    public function __construct(
        private readonly SpawnUniverseAction $spawnAction,
        private readonly TickUniverseAction $tickAction,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'world_id' => 'required|uuid',
            'name' => 'nullable|string|max:255',
            'seed' => 'nullable|integer',
            'parameters' => 'nullable|array',
            'parameters.dimension' => 'required_with:parameters|integer|min:1',
            'parameters.aMatrix' => 'nullable|array',
            'parameters.lMatrix' => 'nullable|array',
        ]);

        try {
            $dto = new SpawnUniverseDTO(
                worldId: $request->input('world_id'),
                name: $request->input('name', 'New Universe'),
                seed: $request->input('seed'),
                parameters: $request->input('parameters', [
                    'dimension' => 2,
                    'aMatrix' => [0.0, 0.0, 0.0, 0.0],
                    'lMatrix' => [1.0, -1.0, -1.0, 1.0],
                    'alpha' => 0.1,
                    'lambda' => 0.5,
                    'eta' => 0.01,
                    'beta' => 1.0,
                    'deltaTarget' => 0.1,
                    'gammaCap' => 2.0,
                    'rMax' => 10.0,
                    'energyRateLimit' => 1.5,
                ]),
            );

            $universe = $this->spawnAction->handle($dto);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $universe->getId()->toString(),
                    'status' => $universe->getStatus()->value,
                ]
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function tick(Request $request, string $id): JsonResponse
    {
        try {
            $dto = new TickUniverseDTO(
                universeId: $id,
            );

            $result = $this->tickAction->handle($dto);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
