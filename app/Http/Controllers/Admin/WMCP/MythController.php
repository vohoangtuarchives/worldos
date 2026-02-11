<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\WorldMyth;
use App\Models\World;
use Illuminate\Http\Request;

class MythController extends Controller
{
    /**
     * List all myths (filterable by world)
     */
    public function index(Request $request)
    {
        $worldId = $request->get('world_id');

        $query = WorldMyth::with('world');

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $myths = $query->orderByDesc('strength')
            ->orderBy('status')
            ->paginate(30);

        $worlds = World::orderBy('name')->get();

        return view('admin.wmcp.myths.index', compact('myths', 'worlds', 'worldId'));
    }

    /**
     * Show myth details
     */
    public function show($id)
    {
        $myth = WorldMyth::with(['world', 'beliefs'])->findOrFail($id);

        return view('admin.wmcp.myths.show', compact('myth'));
    }
}
