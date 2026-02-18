<?php

namespace App\Http\Controllers\Api\Writer;

use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use Illuminate\Http\Request;

class WriterCosmologyController extends Controller
{
    private \App\Domains\WriterConsole\HumanActionValidator $validator;

    public function __construct(
        \App\Domains\WriterConsole\HumanActionValidator $validator,
        private readonly CosmologyRepository $cosmologyRepository
    ) {
        $this->validator = $validator;
    }

    /**
     * List all Sagas for the writer.
     */
    public function getSagas()
    {
        $sagas = Saga::withCount('sagaWorlds')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($sagas);
    }

    /**
     * Create a new Saga from the current active multiversal state.
     * Saga is tied to World (aggregate root). Only universes with world_id are used; distinct world_ids become saga_worlds.
     */
    public function createFromActive()
    {
        $activeUniverses = \App\Models\UniverseModel::where('is_archived', false)->whereNotNull('world_id')->get();
        $worldIds = $activeUniverses->pluck('world_id')->unique()->filter()->values();

        if ($worldIds->isEmpty()) {
            return response()->json([
                'error' => 'No universes linked to a World. Link instances to Worlds first or create instances from a World.',
            ], 400);
        }

        $firstWorld = \App\Models\World::find($worldIds->first());
        $sagaName = $firstWorld ? "Saga of " . $firstWorld->name : "Saga of the Era";

        $saga = Saga::create([
            'name' => $sagaName,
            'status' => 'pending',
            'world_count' => $worldIds->count(),
            'carry_legacy' => true,
            'genre' => 'cosmic-horror',
        ]);

        foreach ($worldIds->values() as $idx => $worldId) {
            \App\Domains\Saga\SagaWorld::create([
                'saga_id' => $saga->id,
                'world_id' => (int) $worldId,
                'status' => 'ACTIVE',
                'sequence' => $idx + 1,
            ]);
        }

        return response()->json($saga);
    }

    /**
     * Get the Yggdrasil Tree data for a specific Saga.
     */
    public function getSagaTree($id)
    {
        $saga = Saga::findOrFail($id);
        $sagaWorlds = $saga->sagaWorlds()->with('world')->get();
        
        $nodes = $sagaWorlds->map(function ($sw) {
            $w = $sw->world;
            if (!$w) {
                return null;
            }
            $runtime = $this->cosmologyRepository->getRuntimeStateForWorld((int) $w->id);
            $currentYear = $runtime !== null ? $runtime['age'] : (int) ($w->current_time ?? 0);
            return [
                'id' => $w->id,
                'parentId' => $w->parent_id,
                'name' => $w->name,
                'status' => $sw->status,
                'current_era' => (int) floor($currentYear / 50),
                'has_collapsed' => $sw->status === 'COLLAPSED',
                'sequence' => $sw->sequence,
            ];
        })->filter();

        return response()->json([
            'saga_name' => $saga->name,
            'nodes' => $nodes
        ]);
    }

    /**
     * Trigger saga simulation.
     */
    public function runSaga($id)
    {
        $saga = Saga::findOrFail($id);
        
        if ($saga->status === 'COMPLETED') {
             return response()->json(['error' => 'Saga already completed'], 400);
        }

        try {
            \App\Jobs\RunSagaSimulationJob::dispatch($saga);
            return response()->json(['message' => 'Simulation started in background']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Canonize an event in the universe's history.
     */
    public function canonize(Request $request, $universeId)
    {
        $data = $request->validate([
            'event_content' => 'required|string',
            'epoch' => 'required|integer'
        ]);

        $validation = $this->validator->validate('canonize_event', $data);
        if (!$validation->isAllowed()) {
            return response()->json(['error' => $validation->getReason()], 403);
        }

        $universe = \App\Models\UniverseModel::findOrFail($universeId);
        
        // Add to the saga or a dedicated canon field
        $params = $universe->parameters ?? [];
        $canon = $params['canon_events'] ?? [];
        $canon[] = [
            'content' => $data['event_content'],
            'epoch' => $data['epoch'],
            'timestamp' => now()->toIso8601String()
        ];
        $params['canon_events'] = $canon;
        $universe->parameters = $params;
        $universe->save();

        return response()->json([
            'message' => 'Event canonized successfully',
            'canon' => $canon
        ]);
    }

    /**
     * Inject a narrative event into a universe.
     */
    public function injectEvent(Request $request, $universeId)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'severity' => 'required|in:LOW,MEDIUM,HIGH,CALAMITY'
        ]);

        $model = \App\Models\UniverseModel::findOrFail($universeId);
        app(\App\Domains\Cosmology\Services\InterventionService::class)
            ->injectNarrative($model, $data['content'], $data['severity']);

        return response()->json(['message' => 'Narrative injected successfully']);
    }

    /**
     * Bifurcate timeline — split universe into two branches.
     */
    public function bifurcate($universeId)
    {
        $repo = app(\App\Domains\Cosmology\Repositories\CosmologyRepository::class);
        $lifecycle = app(\App\Domains\Cosmology\Services\LifecycleService::class);
        $bifurcation = app(\App\Domains\Cosmology\Services\BifurcationService::class);

        $universe = $repo->find($universeId);
        if (!$universe) {
            return response()->json(['error' => 'Universe not found'], 404);
        }

        $branches = $bifurcation->split($universe);
        $lifecycle->archive($universe, 'BIFURCATION');

        return response()->json([
            'message' => 'Timeline bifurcated',
            'branches' => array_map(fn ($u) => $u->getId(), $branches),
        ]);
    }

    /**
     * Induce collapse — archive universe (structural fracture).
     */
    public function induceCollapse($universeId)
    {
        $repo = app(\App\Domains\Cosmology\Repositories\CosmologyRepository::class);
        $lifecycle = app(\App\Domains\Cosmology\Services\LifecycleService::class);

        $universe = $repo->find($universeId);
        if (!$universe) {
            return response()->json(['error' => 'Universe not found'], 404);
        }

        $lifecycle->archive($universe, 'WRITER_INDUCED_COLLAPSE');

        return response()->json(['message' => 'Universe collapsed. Archived.']);
    }

    /**
     * Get last injected event (for Canonize Last Turn).
     */
    public function getLastInjected($universeId)
    {
        $model = \App\Models\UniverseModel::findOrFail($universeId);
        $last = $model->parameters['last_injected_event'] ?? null;
        return response()->json(['last_injected' => $last]);
    }
}
