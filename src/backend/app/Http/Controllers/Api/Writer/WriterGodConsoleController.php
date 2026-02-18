<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: God Console (metrics + intervene) by world id.
 */
class WriterGodConsoleController extends Controller
{
    /**
     * GET /api/writer/worlds/{id}/god-console/metrics
     */
    public function getMetrics(string $id): JsonResponse
    {
        $world = World::with('state')->find($id);

        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $state = $world->state;
        if (!$state) {
            return response()->json([
                'tick' => (int) ($world->current_tick ?? 0),
                'state_vector' => [],
                'phase' => 'unknown',
                'message' => 'World has no state kernel yet.',
            ]);
        }

        return response()->json([
            'tick' => (int) ($world->current_tick ?? 0),
            'state_vector' => $state->state_vector ?? [],
            'phase' => $state->current_phase ?? 'unknown',
        ]);
    }

    /**
     * POST /api/writer/worlds/{id}/god-console/intervene
     * Body: { "action": "inject_belief"|"smite"|"stabilize"|"accelerate" }
     */
    public function intervene(Request $request, string $id): JsonResponse
    {
        $world = World::with('state')->find($id);

        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $state = $world->state;
        if (!$state) {
            return response()->json(['error' => 'World has no state kernel'], 400);
        }

        $action = $request->input('action');
        $vector = $state->state_vector ?? [];

        switch ($action) {
            case 'inject_belief':
                $vector['belief_mass'] = min(1.0, ($vector['belief_mass'] ?? 0) + 0.1);
                $vector['coherence'] = min(1.0, ($vector['coherence'] ?? 0) + 0.05);
                $message = 'Divine Revelation granted! Belief increased.';
                break;
            case 'smite':
                $vector['entropy'] = min(1.0, ($vector['entropy'] ?? 0) + 0.2);
                $vector['stability'] = max(0.0, ($vector['stability'] ?? 0) - 0.15);
                $message = 'Disaster struck! Entropy rose.';
                break;
            case 'stabilize':
                $vector['stability'] = min(1.0, ($vector['stability'] ?? 0) + 0.2);
                $vector['entropy'] = max(0.0, ($vector['entropy'] ?? 0) - 0.1);
                $message = 'Order restored. Stability increased.';
                break;
            case 'accelerate':
                $vector['resource_flow'] = min(1.0, ($vector['resource_flow'] ?? 0) + 0.15);
                $vector['innovation_rate'] = min(1.0, ($vector['innovation_rate'] ?? 0) + 0.1);
                $message = 'Golden Age initiated! Innovation booming.';
                break;
            default:
                return response()->json(['error' => 'Unknown intervention action'], 422);
        }

        $state->state_vector = $vector;
        $state->save();

        return response()->json(['success' => true, 'message' => $message]);
    }
}
