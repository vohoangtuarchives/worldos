<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\World;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncidentController extends Controller
{
    public function index()
    {
        $incidents = IncidentReport::with('world')->latest()->get();
        return view('admin.wmcp.incidents.index', compact('incidents'));
    }

    public function create(World $world)
    {
        return view('admin.wmcp.incidents.create', compact('world'));
    }

    public function store(Request $request, World $world)
    {
        $validated = $request->validate([
            'severity' => 'required|in:CRITICAL,HIGH,MEDIUM,LOW',
            'summary' => 'required|string',
        ]);

        $incidentId = 'INC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(3));

        $incident = IncidentReport::create([
            'incident_id' => $incidentId,
            'world_id' => $world->id,
            'status' => 'DETECTED', // Start at Detect phase
            'severity' => $validated['severity'],
            'summary' => $validated['summary'],
            'timeline_events' => [
                ['time' => 'T-0', 'event' => 'Incident Detected / Manual Creation']
            ]
        ]);

        return redirect()->route('admin.wmcp.incidents.show', $incident);
    }

    public function show(IncidentReport $incident)
    {
        return view('admin.wmcp.incidents.show', compact('incident'));
    }

    public function update(Request $request, IncidentReport $incident)
    {
        // This handles updates during the Post-Mortem Analysis phase
        $validated = $request->validate([
            'status' => 'required|string',
            'root_cause' => 'nullable|string',
            'resolution_justification' => 'nullable|string',
            'timeline_events' => 'nullable|array',
            'five_whys' => 'nullable|array',
            'action_items' => 'nullable|array',
            'impact_assessment' => 'nullable|array',
        ]);

        $incident->update($request->all()); // Quick update for now, refine later

        return back()->with('success', 'Incident Report updated.');
    }
}
