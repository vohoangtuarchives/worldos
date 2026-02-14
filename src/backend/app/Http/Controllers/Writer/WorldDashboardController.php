<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Domains\World\Myth;
use App\Domains\World\Scar;
use App\Domains\World\Services\EventGate;
use App\Domains\Power\Services\WorldPressureService;
use App\Domains\World\Memory\ContradictionMemory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorldDashboardController extends Controller
{
    public function __construct(
        private WorldPressureService $pressureService,
        private EventGate $eventGate
    ) {}

    public function index(Request $request)
    {
        $worldId = $request->query('world_id', 1); // Default to world 1

        // 1. Fetch Power State
        $powerState = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $currentPressure = $this->pressureService->calculatePressure($worldId);

        // 2. Fetch Deep Logic Objects
        $myths = Myth::where('world_id', $worldId)->get();
        $scars = Scar::where('world_id', $worldId)->get();
        
        // 3. Fetch Memory (Recent Resolutions)
        $memories = ContradictionMemory::where('world_id', $worldId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // 4. Fetch Recent Ledger Events
        $recentEvents = DB::table('world_event_ledger')
            ->where('world_id', $worldId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('writer.world.dashboard', compact(
            'worldId',
            'powerState',
            'currentPressure',
            'myths',
            'scars',
            'memories',
            'recentEvents'
        ));
    }

    public function injectEvent(Request $request)
    {
        $data = $request->validate([
            'world_id' => 'required|integer',
            'event_type' => 'required|string',
            'description' => 'required|string',
            'magnitude' => 'required|numeric|min:0|max:1',
            'permanence' => 'required|numeric|min:0|max:1',
            'visibility' => 'required|string',
            'is_contradiction' => 'boolean',
            'contradiction_id' => 'nullable|string',
            'severity' => 'nullable|string'
        ]);

        // Process through Gate
        $result = $this->eventGate->processEvent($data['world_id'], $data);

        if ($result['allowed']) {
            return back()->with('success', "Event Injected: " . $result['action']);
        } else {
            return back()->with('error', "Event Blocked by Gate: " . $result['reason']);
        }
    }

    public function createScar(Request $request)
    {
        $data = $request->validate([
            'world_id' => 'required|integer',
            'location_scope' => 'required|string',
            'constraint_rule' => 'required|string',
            'severity' => 'required|numeric|min:0|max:1'
        ]);

        Scar::create($data);

        return back()->with('success', 'Divine Scar successfully branded upon the world.');
    }
}
