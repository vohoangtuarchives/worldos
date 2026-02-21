<?php

namespace App\Http\Controllers\Api\V4\Writer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use WorldOS\Applications\Simulator\IgniteUniverseUseCase;

class WriterGenesisController extends Controller
{
    public function __construct(
        private IgniteUniverseUseCase $igniteUniverseUseCase
    ) {}

    public function createUniverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seed_name' => 'required|string',
            'archetype' => 'required|string',
            'ontology' => 'required|numeric',
            'epistemic' => 'required|numeric',
            'civilization' => 'required|numeric',
            'energy' => 'required|numeric',
        ]);

        $universeId = uniqid('uni_');

        $this->igniteUniverseUseCase->execute(
            $universeId,
            $validated['archetype'],
            $validated['ontology'],
            $validated['epistemic'],
            $validated['civilization'],
            $validated['energy']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Universe ignited successfully',
            'data' => [
                'world_id' => $universeId,
                'seed' => $validated['seed_name'],
            ]
        ], 201);
    }
}
