<?php

namespace App\Http\Controllers;

use App\Models\World;
use App\Domains\Narrative\Observers\Observer;
use App\Domains\Narrative\Services\NarrativeService;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        protected NarrativeService $narrative
    ) {}

    public function __invoke(Request $request)
    {
        $cursor = (int) $request->query('cursor', 0);
        $observerName = $request->query('observer', 'chronicler');

        $world = World::first();

        if (! $world) {
            return view('story.empty');
        }

        // Find Observer and their latest Version
        $observer = \App\Models\Observer::where('name', $observerName)->first();
        
        if (! $observer) {
            abort(404, "Observer '$observerName' not found.");
        }

        $version = $observer->versions()->first(); // Simplified: just get first version

        $slice = $this->narrative->project(
            world: $world,
            observerVersion: $version,
            fromTick: $cursor,
            limit: 10
        );

        return view('story.read', [
            'story' => $slice
        ]);
    }
}
