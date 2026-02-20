<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\Request;
use Tuzy\Domain\World\Exception\WorldNotFoundException;

class GodConsoleController extends Controller
{
    /**
     * Display the God Console Dashboard.
     */
    public function index($worldId)
    {
        $world = World::with(['state', 'state.evolutionProfile'])->find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        
        // Ensure state exists (it should if created via Genesis)
        if (!$world->state) {
            return redirect()->back()->with('error', 'World has no active state kernel.');
        }

        return view('writer.god_console.dashboard', compact('world'));
    }

    /**
     * Get real-time metrics (JSON) for charts.
     */
    public function getMetrics($worldId)
    {
        $world = World::with('state')->find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        
        return response()->json([
            'tick' => $world->current_tick,
            'state_vector' => $world->state->state_vector ?? [],
            'phase' => $world->state->current_phase ?? 'unknown',
        ]);
    }

    /**
     * Handle manual interventions (God Powers).
     */
    public function intervene(Request $request, $worldId)
    {
        $world = World::with('state')->find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId((string) $worldId);
        }
        $state = $world->state;
        
        $action = $request->input('action');
        $vector = $state->state_vector ?? []; // Array
        
        // Simple direct modification for Phase 1
        switch ($action) {
            case 'inject_belief':
                $vector['belief_mass'] = min(1.0, ($vector['belief_mass'] ?? 0) + 0.1);
                $vector['coherence'] = min(1.0, ($vector['coherence'] ?? 0) + 0.05);
                $message = "Divine Revelation granted! Belief increased.";
                break;
                
            case 'smite':
                $vector['entropy'] = min(1.0, ($vector['entropy'] ?? 0) + 0.2);
                $vector['stability'] = max(0.0, ($vector['stability'] ?? 0) - 0.15);
                $message = "Disaster struck! Entropy rose.";
                break;
                
            case 'stabilize':
                $vector['stability'] = min(1.0, ($vector['stability'] ?? 0) + 0.2);
                $vector['entropy'] = max(0.0, ($vector['entropy'] ?? 0) - 0.1);
                $message = "Order restored. Stability increased.";
                break;
                
            case 'accelerate':
                $vector['resource_flow'] = min(1.0, ($vector['resource_flow'] ?? 0) + 0.15);
                $vector['innovation_rate'] = min(1.0, ($vector['innovation_rate'] ?? 0) + 0.1);
                $message = "Golden Age initiated! Innovation booming.";
                break;
                
            default:
                return redirect()->back()->with('error', 'Unknown intervention.');
        }

        // Save
        $state->state_vector = $vector;
        $state->save();

        // Log it (Todo: GovernanceLog)

        return redirect()->back()->with('success', $message);
    }
}
