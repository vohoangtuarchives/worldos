<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\WorldManagement\Repositories\AIGovernanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriterAIAgentController extends Controller
{
    public function __construct(
        protected AIGovernanceRepository $repository
    ) {}

    public function getMetrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getGlobalMetrics(),
        ]);
    }

    public function getGenerations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getRecentGenerations(),
        ]);
    }

    public function getAgents(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getAgentStats(),
        ]);
    }

    public function intervene(Request $request): JsonResponse
    {
        // Simple intervention logging/stub
        $worldId = $request->input('world_id');
        $instruction = $request->input('instruction');

        return response()->json([
            'success' => true,
            'message' => "Divine Inspiration transmitted to World {$worldId}: \"{$instruction}\"",
        ]);
    }
}
