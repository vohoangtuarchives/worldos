<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Narrative\Models\MaterialSeed;
use App\Domains\Narrative\Models\StoryPremise;
use App\Domains\Narrative\Services\MaterialGenerator;
use Illuminate\Http\Request;

class MaterialLibraryController extends Controller
{
    protected MaterialGenerator $generator;

    public function __construct(MaterialGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function index()
    {
        // Fetch seeds for display
        $seeds = MaterialSeed::all()->groupBy('type');
        
        // Fetch recent premises
        $premises = StoryPremise::latest()->take(5)->get();

        return view('writer.materials.index', compact('seeds', 'premises'));
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['power_system', 'social_structure', 'twist', 'hidden_truth']);
        
        // Remove empty filters
        $filters = array_filter($filters);

        $premise = $this->generator->generatePremise($filters);

        return redirect()->route('writer.materials.library')
            ->with('generated_premise', $premise);
    }

    public function save($id)
    {
        $premise = StoryPremise::findOrFail($id);
        $premise->is_favorite = true;
        $premise->save();

        return back()->with('success', 'Premise saved to library!');
    }
}
