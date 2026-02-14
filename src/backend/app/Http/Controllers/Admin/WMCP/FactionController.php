<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\World;
use Illuminate\Http\Request;

class FactionController extends Controller
{
    public function index(World $world)
    {
        $factions = $world->factions()->get();
        return view('admin.wmcp.factions.index', compact('world', 'factions'));
    }

    public function create(World $world)
    {
        return view('admin.wmcp.factions.create', compact('world'));
    }

    public function store(Request $request, World $world)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'attributes' => 'nullable|array', // Configurable attributes (cohesion, etc)
            'attributes.cohesion' => 'nullable|integer|min:0|max:100',
        ]);

        $world->factions()->create($validated);

        return redirect()->route('admin.wmcp.worlds.show', $world)->with('success', 'Faction created successfully.');
    }

    public function edit(Faction $faction)
    {
        $world = $faction->world;
        return view('admin.wmcp.factions.edit', compact('faction', 'world'));
    }

    public function update(Request $request, Faction $faction)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'attributes' => 'nullable|array',
            'attributes.cohesion' => 'nullable|integer|min:0|max:100',
        ]);

        $faction->update($validated);

        return redirect()->route('admin.wmcp.worlds.show', $faction->world)->with('success', 'Faction updated successfully.');
    }

    public function destroy(Faction $faction)
    {
        $world = $faction->world;
        $faction->delete();
        return back()->with('success', 'Faction deleted successfully.');
    }
}
