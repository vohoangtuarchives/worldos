<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Domains\Cosmic\Services\EpochControlService;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use App\Domains\Cosmic\Services\EmergencyInterventionService;
use App\Domains\World\Scar;
use App\Domains\World\Services\EventGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: World Hub actions (freeze, resume, step, rollback, inject, scar, emergency).
 */
class WriterWorldHubController extends Controller
{
    public function __construct(
        private EpochControlService $epochControl,
        private EmergencyInterventionService $emergencyService,
        private EventGate $eventGate,
    ) {}

    public function freeze(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        try {
            $this->epochControl->freeze($world);
            return response()->json(['success' => true, 'message' => 'World frozen.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function resume(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        try {
            $this->epochControl->resume($world);
            return response()->json(['success' => true, 'message' => 'World resumed.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function step(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        try {
            $result = $this->epochControl->stepEpoch($world);
            return response()->json([
                'success' => true,
                'message' => 'Stepped 1 epoch.',
                'epoch' => $result['epoch'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function rollback(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        try {
            $snapshot = $this->epochControl->rollback($world);
            return response()->json([
                'success' => true,
                'message' => 'Rolled back.',
                'year' => $snapshot->year ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function inject(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        $data = $request->validate([
            'event_type' => 'required|string',
            'description' => 'required|string',
            'magnitude' => 'required|numeric|min:0|max:1',
            'permanence' => 'required|numeric|min:0|max:1',
            'visibility' => 'required|string',
        ]);
        $data['world_id'] = (string) $id;
        $result = $this->eventGate->processEvent($id, $data);
        if ($result['allowed'] ?? false) {
            return response()->json(['success' => true, 'message' => 'Event injected: ' . ($result['action'] ?? 'ok')]);
        }
        return response()->json(['error' => 'Event blocked: ' . ($result['reason'] ?? 'unknown')], 422);
    }

    public function scar(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }
        $data = $request->validate([
            'location_scope' => 'required|string',
            'constraint_rule' => 'required|string',
            'severity' => 'required|numeric|min:0|max:1',
        ]);
        $data['world_id'] = (string) $id;
        Scar::create($data);
        return response()->json(['success' => true, 'message' => 'Divine Scar branded upon the world.']);
    }

    /**
     * WorldOS v3: Emergency interventions target the active Universe (state_vector)
     * instead of the legacy cosmic_snapshots table.
     */
    public function emergency(Request $request, string $id, string $action): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            throw WorldNotFoundException::withId($id);
        }

        // V3: Find the active Universe for this World
        $universe = \App\Models\UniverseModel::where('world_id', $id)
            ->where('is_archived', false)
            ->first();

        if (!$universe) {
            return response()->json(['error' => 'No active universe found for this world. Create a Universe instance first.'], 422);
        }

        try {
            $result = match ($action) {
                'entropy-shock' => $this->emergencyService->injectEntropyShockV3($universe, (float) $request->input('magnitude', 0.15)),
                'reduce-rigidity' => $this->emergencyService->reduceRigidityV3($universe, (float) $request->input('reduction', 0.1)),
                'force-collapse' => $this->emergencyService->forceCollapseV3($universe),
                'toggle-emergent' => (function () use ($request) {
                    $this->emergencyService->setEmergentArchetypesDisabled((bool) $request->input('disabled', false));
                    return null;
                })(),
                default => throw new \InvalidArgumentException('Unknown action'),
            };

            return response()->json([
                'success' => true,
                'message' => "Emergency '{$action}' executed on Universe " . substr($universe->id, 0, 8) . ".",
                'universe_id' => $universe->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
