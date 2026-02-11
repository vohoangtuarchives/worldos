<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\WorldEvent;
use App\Models\World;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = WorldEvent::with('world:id,name')
            ->orderBy('created_at', 'desc');

        // Filter by World
        if ($request->filled('world_id')) {
            $query->where('world_id', $request->world_id);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }

        // Filter by Tick Range
        if ($request->filled('tick_from')) {
            $query->where('tick', '>=', $request->tick_from);
        }
        if ($request->filled('tick_to')) {
            $query->where('tick', '<=', $request->tick_to);
        }

        // Filter by Timeline
        if ($request->filled('timeline_id')) {
            $query->where('timeline_id', $request->timeline_id);
        }

        $events = $query->paginate(50);
        $worlds = World::orderBy('name')->get(['id', 'name']);

        return view('admin.wmcp.events.index', compact('events', 'worlds'));
    }

    public function show($id)
    {
        $event = WorldEvent::with('world')->findOrFail($id);
        
        return view('admin.wmcp.events.show', compact('event'));
    }

    public function export(Request $request)
    {
        $query = WorldEvent::query();

        // Apply same filters as index
        if ($request->filled('world_id')) {
            $query->where('world_id', $request->world_id);
        }
        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }
        if ($request->filled('timeline_id')) {
            $query->where('timeline_id', $request->timeline_id);
        }

        $events = $query->orderBy('chapter')->get();

        return response()->json($events, 200, [], JSON_PRETTY_PRINT)
            ->header('Content-Disposition', 'attachment; filename="world_events_' . now()->format('Y-m-d_His') . '.json"');
    }
}
