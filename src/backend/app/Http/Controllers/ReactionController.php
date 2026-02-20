<?php

namespace App\Http\Controllers;

use App\Models\World;
use App\Models\ReaderSession;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use App\Models\ReaderReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReactionController extends Controller
{
    /**
     * Submit a reaction to a specific moment in history.
     */
    public function store(Request $request, string $worldId)
    {
        $request->validate([
            'tick' => 'required|integer',
            'type' => 'required|string|in:candle,flower,dread,hope',
            'session_id' => 'required|uuid', // In real app, this comes from cookie/header
        ]);

        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }

        // Find or create session
        $session = ReaderSession::firstOrCreate(
            ['id' => $request->input('session_id')],
            [
                'world_id' => $world->id,
                'last_active_at' => now(),
            ]
        );

        // Record reaction
        $reaction = ReaderReaction::create([
            'session_id' => $session->id,
            'world_id' => $world->id,
            'tick' => $request->input('tick'),
            'type' => $request->input('type'),
        ]);

        return response()->json(['status' => 'success', 'id' => $reaction->id], 201);
    }

    /**
     * Get aggregated reactions for a world.
     */
    public function index(string $worldId)
    {
        $counts = ReaderReaction::where('world_id', $worldId)
            ->selectRaw('tick, type, count(*) as count')
            ->groupBy('tick', 'type')
            ->get();

        return response()->json($counts);
    }
}
