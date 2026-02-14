<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;
use App\Domains\Historian\SagaAnalyzer;
use App\Domains\WriterConsole\WriterFacingAPI;
use App\Domains\Saga\SagaRunner;
use App\Models\GateChannel;
use App\Domains\World\ValueObjects\PhysicsProfile;

class SagaExplorerController extends Controller
{
    private SagaAnalyzer $analyzer;
    private WriterFacingAPI $writerApi;
    private SagaRunner $sagaRunner;
    private \App\Domains\Material\Contracts\MaterialRepositoryInterface $materialRepo;

    public function __construct(
        SagaAnalyzer $analyzer, 
        WriterFacingAPI $writerApi,
        SagaRunner $sagaRunner,
        \App\Domains\Material\Contracts\MaterialRepositoryInterface $materialRepo
    ) {
        $this->analyzer = $analyzer;
        $this->writerApi = $writerApi;
        $this->sagaRunner = $sagaRunner;
        $this->materialRepo = $materialRepo;
    }

    /**
     * Trigger a pending Saga
     */
    public function run(Saga $saga)
    {
        // Check if saga is completed but has pending worlds (premature completion)
        if ($saga->isComplete()) {
            $hasPending = $saga->sagaWorlds()->where('status', \App\Domains\Saga\SagaWorld::STATUS_PENDING)->exists();
            if ($hasPending) {
                $saga->update(['status' => \App\Domains\Saga\Saga::STATUS_RUNNING]);
                // We don't return error, we let it proceed to dispatch job
            } else {
                return back()->withErrors(['error' => 'Saga is already completed.']);
            }
        }
        
        // Allow resuming if running/failed/pending

        // Run asynchronously via Job to prevent browser timeout
        try {
            \App\Jobs\RunSagaSimulationJob::dispatch($saga);
            return back()->with('success', 'Simulation started in background. Please wait for completion.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start simulation: ' . $e->getMessage()]);
        }
    }

    /**
     * List all Sagas
     */
    public function index()
    {
        $sagas = Saga::orderBy('created_at', 'desc')->paginate(10);
        return view('writer.saga.index', compact('sagas'));
    }

    /**
     * Show single Saga details (Historian View)
     */
    public function show(Saga $saga)
    {
        // If complete, show full analysis
        $analysis = null;
        if ($saga->isComplete()) {
            $analysis = $this->analyzer->analyze($saga);
        }

        return view('writer.saga.show', [
            'saga' => $saga,
            'analysis' => $analysis,
            'worlds' => $saga->sagaWorlds()->with('world')->get()
        ]);
    }

    /**
     * Drill down into a specific World within a Saga
     */
    public function showWorld(Saga $saga, $worldSequence)
    {
        $sagaWorld = $saga->sagaWorlds()
            ->where('sequence', $worldSequence)
            ->with('world')
            ->firstOrFail();

        // Convert technical state to writer terms
        // Assuming we can derive state from world data or snapshots
        // For now, using placeholder state construction
        $technicalState = [
            'archetypes' => [], // Populate from real data
            'legitimacy' => $sagaWorld->collapse_context['legitimacy'] ?? 1.0
        ];
        
        $writerState = $this->writerApi->getWorldState($technicalState);

        $chronicles = \Illuminate\Support\Facades\DB::table('chronicles')
            ->where('world_id', $sagaWorld->world_id)
            ->orderBy('epoch', 'asc')
            ->get();

        $story = \App\Models\Story::where('world_id', $sagaWorld->world_id)->first();
        
        // Fetch Material Instances
        $materials = $this->materialRepo->getInstancesForWorld($sagaWorld->world_id);

        // --- MULTIVERSE AUTOMATION DATA ---
        $gates = GateChannel::where('source_world_id', $sagaWorld->world_id)
            ->orWhere('target_world_id', $sagaWorld->world_id)
            ->with(['sourceWorld', 'targetWorld'])
            ->get();

        $baseline = $sagaWorld->world->preset === 'void' 
            ? PhysicsProfile::void() 
            : PhysicsProfile::standard();
            
        $drift = $sagaWorld->world->physics_profile->calculateDrift($baseline);

        return view('writer.saga.world', [
            'saga' => $saga,
            'sagaWorld' => $sagaWorld,
            'writerState' => $writerState,
            'chronicles' => $chronicles,
            'story' => $story,
            'materials' => $materials,
            'gates' => $gates,
            'realityDrift' => $drift
        ]);
    }
    /**
     * Show Yggdrasil Tree Visualization for Saga
     */
    public function tree(Saga $saga)
    {
        // Fetch all worlds in this saga with checking parent relationships
        $sagaWorlds = $saga->sagaWorlds()->with('world')->get();
        
        // Prepare data for D3
        $nodes = $sagaWorlds->map(function($sw) {
            $w = $sw->world;
            return [
                'id' => $w->id,
                'parentId' => $w->parent_id, // Root world has null
                'name' => $w->name,
                'origin_type' => $w->origin_type ?? 'cosmic',
                'current_era' => (int) floor(($w->current_time ?? 0) / 50),
                'bifurcation_trigger' => $w->bifurcation_trigger, // e.g. "hero:5"
                'status' => $sw->status,
                'has_collapsed' => $sw->hasCollapsed(),
                'sequence' => $sw->sequence
            ];
        });

        return view('writer.saga.tree', [
            'saga' => $saga,
            'treeData' => $nodes
        ]);
    }
}
