<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Saga\Services\GenesisPresetService;
use App\Jobs\RunSagaSimulationJob;
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
        private GenesisPresetService $presetService
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
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
            'name' => 'required|string|max:255',
            'preset_key' => 'nullable|string',
            'genre' => 'nullable|string',
            'power_system' => 'nullable|string',
            'power_ceiling' => 'nullable|string',
            'tech_level' => 'nullable|string',
            'environment' => 'nullable|string',
            'social_structure' => 'nullable|string',
            'starting_crisis' => 'nullable|string',
            'power_ranking' => 'nullable|string',
            'origin_type' => 'nullable|string',
            'world_count' => 'integer|min:1|max:20',
            'carry_legacy' => 'boolean',
        ]);

        $config = [];
        if (!empty($validated['preset_key'])) {
            $preset = $this->presetService->find($validated['preset_key']);
            if ($preset) {
                $config = $preset;
            }
        }

        foreach (['genre', 'power_system', 'power_ceiling', 'tech_level', 'environment', 'social_structure', 'starting_crisis', 'power_ranking'] as $field) {
            if (!empty($validated[$field])) {
                $config[$field] = $validated[$field];
            }
        }

        // Default 1 world per saga; multi-world (Pareto/evolution) is opt-in via world_count > 1
        $saga = Saga::create([
            'name' => $validated['name'],
            'world_count' => $validated['world_count'] ?? 1,
            'carry_legacy' => $validated['carry_legacy'] ?? true,
            'genre' => $config['genre'] ?? 'xianxia',
            'status' => Saga::STATUS_PENDING,
            'metadata' => [
                'origin_type' => $validated['origin_type'] ?? 'cosmic',
                'genesis_preset' => $validated['preset_key'] ?? 'custom',
                'power_system' => $config['power_system'] ?? 'NONE',
                'power_ceiling' => $config['power_ceiling'] ?? 'HUMAN',
                'tech_level' => $config['tech_level'] ?? 'DYNASTIC',
                'environment' => $config['environment'] ?? 'CONTINENTAL',
                'social_structure' => $config['social_structure'] ?? 'EMPIRE',
                'starting_crisis' => $config['starting_crisis'] ?? 'NONE',
                'power_ranking' => $config['power_ranking'] ?? 'NATURAL',
            ],
        ]);

        RunSagaSimulationJob::dispatch($saga);

            return response()->json([
                'saga_id' => $saga->id,
                'name' => $saga->name,
                'message' => 'Khai Thiên Tịch Địa! Thế giới mới đã được sáng tạo và đang bắt đầu mô phỏng.',
            ], 201);
        } catch (Throwable $e) {
            Log::error('WriterGenesisController::store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $message = config('app.debug') ? $e->getMessage() : 'Tạo Saga thất bại. Xem storage/logs/laravel.log.';
            return response()->json(['error' => $message], 500);
        }
    }
}
