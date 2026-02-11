<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\WorldScar;
use App\Models\World;
use Illuminate\Http\Request;

class ScarController extends Controller
{
    /**
     * List all scars (filterable by world)
     */
    public function index(Request $request)
    {
        $worldId = $request->get('world_id');

        $query = WorldScar::with(['world', 'sourceEvent']);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $scars = $query->orderByDesc('weight')
            ->orderByDesc('created_at')
            ->paginate(30);

        $worlds = World::orderBy('name')->get();

        return view('admin.wmcp.scars.index', compact('scars', 'worlds', 'worldId'));
    }

    /**
     * Show scar details
     */
    public function show($id)
    {
        $scar = WorldScar::with(['world', 'sourceEvent'])->findOrFail($id);

        return view('admin.wmcp.scars.show', compact('scar'));
    }
}
