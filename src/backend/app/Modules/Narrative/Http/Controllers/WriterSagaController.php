<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Http\Controllers;

use App\Modules\Narrative\Actions\AdvanceSagaAction;
use App\Modules\Narrative\Actions\CreateSagaAction;
use App\Modules\Narrative\Dto\CreateSagaDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer Saga Controller — Saga management endpoints.
 *
 * From docs §13.1: POST /api/writer/saga/advance, POST /api/writer/sagas/create-from-active
 *
 * Thin controller.
 */
class WriterSagaController extends \App\Http\Controllers\Controller
{
    /**
     * POST /api/writer/saga/advance
     *
     * Advance all Universes in a Saga by N ticks.
     */
    public function advance(
        Request $request,
        AdvanceSagaAction $advanceAction,
    ): JsonResponse {
        $request->validate([
            'saga_id' => 'required|uuid',
            'ticks' => 'nullable|integer|min:1|max:1000',
        ]);

        $results = $advanceAction->handle(
            sagaId: $request->input('saga_id'),
            ticks: (int) $request->input('ticks', 1),
        );

        return response()->json($results, 202);
    }

    /**
     * POST /api/writer/sagas/create-from-active
     *
     * Create a new Saga with active configuration.
     */
    public function createFromActive(
        Request $request,
        CreateSagaAction $createAction,
    ): JsonResponse {
        $request->validate([
            'name' => 'required|string|max:255',
            'preset_key' => 'nullable|string',
            'universe_ids' => 'nullable|array',
            'universe_ids.*' => 'uuid',
        ]);

        $dto = new CreateSagaDTO(
            name: $request->input('name'),
            presetKey: $request->input('preset_key'),
            universeIds: $request->input('universe_ids', []),
        );

        $saga = $createAction->handle($dto);

        return response()->json([
            'saga_id' => $saga->getId()->value,
            'name' => $request->input('name'),
            'status' => $saga->getStatus()->value,
            'message' => 'Saga created successfully',
        ], 201);
    }


}
