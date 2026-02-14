<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Evolution\Services\GenesisService;
use App\Domains\Narrative\Models\StoryPremise;
use Illuminate\Http\Request;

class WorldGenesisController extends Controller
{
    protected GenesisService $genesis;

    public function __construct(GenesisService $genesis)
    {
        $this->genesis = $genesis;
    }

    /**
     * Incarnate a premise into a world.
     */
    public function store(Request $request, $id)
    {
        $premise = StoryPremise::findOrFail($id);

        try {
            $world = $this->genesis->incarnate($premise);

            // Redirect to the God Console for this new world
            return redirect()->route('writer.god_console.index', $world->id)
                ->with('success', "World '{$world->name}' incarnated successfully!");
            
        } catch (\Exception $e) {
            \Log::error("Genesis failed: " . $e->getMessage());
            return back()->with('error', 'Failed to incarnate world: ' . $e->getMessage());
        }
    }
}
