<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\WorldOS\Saga\Actions\AdvanceSagaAction;
use App\WorldOS\Saga\Actions\CreateSagaAction;
use App\WorldOS\Saga\Dto\CreateSagaDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer Saga Controller — Saga management endpoints.
 *
 * From docs §13.1: POST /api/writer/saga/advance, POST /api/writer/sagas/create-from-active
 *
 * Thin controller.
 */
class WriterSagaController extends Controller
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

        return response()->json([
            'saga_id' => $request->input('saga_id'),
            'ticks_requested' => (int) $request->input('ticks', 1),
            'universes_advanced' => count($results),
            'results' => $this->formatResults($results),
        ]);
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
        ]);

        $dto = new CreateSagaDTO(
            name: $request->input('name'),
            presetKey: $request->input('preset_key'),
        );

        $saga = $createAction->handle($dto);

        return response()->json([
            'saga_id' => $saga->getId()->value,
            'name' => $request->input('name'),
            'status' => $saga->getStatus()->value,
            'message' => 'Saga created successfully',
        ], 201);
    }

    /**
     * @param array<string, array> $results
     * @return array<string, array>
     */
    private function formatResults(array $results): array
    {
        $formatted = [];

        foreach ($results as $universeId => $tickResults) {
            $formatted[$universeId] = [
                'ticks_completed' => count($tickResults),
                'final_entropy' => !empty($tickResults)
                    ? end($tickResults)->stateVector->entropy ?? null
                    : null,
            ];
        }

        return $formatted;
    }
}
