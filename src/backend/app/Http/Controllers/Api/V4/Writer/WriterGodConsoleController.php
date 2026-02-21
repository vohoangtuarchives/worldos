<?php

namespace App\Http\Controllers\Api\V4\Writer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use WorldOS\Applications\Simulator\ApplyInterventionUseCase;

class WriterGodConsoleController extends Controller
{
    public function __construct(
        private ApplyInterventionUseCase $applyInterventionUseCase
    ) {}

    public function getMetrics(Request $request, string $worldId): JsonResponse
    {
        // Mocking Read Model logic since CQRS isn't fully implemented yet
        return response()->json([
            'status' => 'success',
            'data' => [
                'system_load' => rand(10, 99),
                'entropy_level' => rand(0, 100) / 100,
                'active_tensions' => [
                    ['domain' => 'nature', 'level' => 0.5, 'source' => 'gravity_anomaly']
                ]
            ]
        ]);
    }

    public function applyTension(Request $request, string $worldId): JsonResponse
    {
        $validated = $request->validate([
            'tensionType' => 'required|string',
            'intensity' => 'required|numeric' // Note frontend uses -0.5 and 0.5
        ]);

        try {
            $this->applyInterventionUseCase->execute(
                $worldId,
                $validated['tensionType'],
                $validated['intensity']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Domain Intervention registered successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
