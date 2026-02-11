<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\Request;
use App\Domains\WorldManagement\Services\SimulationManager;

class SimulationController extends Controller
{
    public function __construct(protected SimulationManager $manager) {}

    public function index()
    {
        $worlds = World::all();
        return view('admin.wmcp.simulation.index', compact('worlds'));
    }

    public function run(Request $request, $worldId)
    {
        $steps = $request->input('steps', 1);
        
        // Use Manager to run simulation
        // In real app, this should queue a Job.
        // For MVP, synchronous run.
        $metrics = $this->manager->runSteps($worldId, $steps);

        return back()->with('success', "Simulation ran for {$steps} steps.");
    }
}
