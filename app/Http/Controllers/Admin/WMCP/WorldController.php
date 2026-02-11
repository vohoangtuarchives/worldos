<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\Request;

class WorldController extends Controller
{
    public function index()
    {
        $worlds = World::with('clock')->get();
        return view('admin.wmcp.worlds.index', compact('worlds'));
    }

    public function show($id)
    {
        $world = World::with(['clock', 'events' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('admin.wmcp.worlds.show', compact('world'));
    }

    public function create()
    {
        return view('admin.wmcp.worlds.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'magic_system' => 'required|string',
            'tech_level' => 'required|string',
            'power_ceiling' => 'required|string',
        ]);

        // Parse tags from comma-separated string
        $tags = $validated['tags'] ?? '';
        $tagsArray = array_filter(array_map('trim', explode(',', $tags)));

        // Create World Law Profile
        $lawProfile = \App\Domains\World\ValueObjects\WorldLawProfile::fromArray([
            'magicSystem' => $validated['magic_system'],
            'techLevel' => $validated['tech_level'],
            'powerCeiling' => $validated['power_ceiling'],
        ]);

        $world = World::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'tags' => $tagsArray,
            'law_profile' => $lawProfile,
            'status' => 'ACTIVE',
            'health_status' => \App\Domains\World\Enums\WorldHealthStatus::STABLE,
        ]);

        // Create initial clock
        $world->clock()->create(['current_tick' => 0]);

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'WORLD_CREATED',
            [
                'world' => $world->name,
                'type' => $world->type->value,
            ],
            'INFO'
        );

        return redirect()->route('admin.wmcp.worlds.show', $world->id)
            ->with('success', "World '{$world->name}' created successfully.");
    }

    public function edit($id)
    {
        $world = World::findOrFail($id);
        return view('admin.wmcp.worlds.edit', compact('world'));
    }

    public function update(Request $request, $id)
    {
        $world = World::findOrFail($id);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        // Parse tags
        $tags = $validated['tags'] ?? '';
        $tagsArray = array_filter(array_map('trim', explode(',', $tags)));

        $world->update([
            'description' => $validated['description'],
            'tags' => $tagsArray,
        ]);

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'WORLD_METADATA_UPDATED',
            ['world' => $world->name],
            'INFO'
        );

        return redirect()->route('admin.wmcp.worlds.show', $world->id)
            ->with('success', 'World metadata updated successfully.');
    }

    public function fork(Request $request, $id, \App\Domains\World\Services\WorldForkService $forkService)
    {
        $world = World::findOrFail($id);
        $validated = $request->validate([
            'new_name' => 'required|string|max:255',
            'tick' => 'required|integer|min:0',
            'justification' => 'required|string|min:10',
        ]);

        // Article V: Fork requires justification
        // Log the fork action with justification
        \Log::info('World Fork Initiated', [
            'operator' => auth()->user()->email ?? 'system',
            'source_world' => $world->name,
            'new_world' => $validated['new_name'],
            'tick' => $validated['tick'],
            'justification' => $validated['justification'],
            'article' => 'Constitution Article V'
        ]);
        
        $newWorld = $forkService->fork($world, $validated['tick'], $validated['new_name']);

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'FORK',
            [
                'source_world' => $world->name,
                'new_world' => $newWorld->name,
                'tick' => $validated['tick'],
                'justification' => $validated['justification'],
            ],
            'INFO'
        );

        return redirect()->route('admin.wmcp.worlds.show', $newWorld->id)
            ->with('success', "Forked '{$newWorld->name}' from '{$world->name}' at tick {$validated['tick']}. Justification logged.");
    }

    public function lock($id)
    {
        $world = World::findOrFail($id);
        $world->update(['status' => 'LOCKED']);
        return back()->with('success', 'World locked successfully.');
    }

    public function unlock($id)
    {
        World::findOrFail($id)->update(['status' => 'ACTIVE']);
        return back()->with('success', 'World unlocked successfully.');
    }

    public function safeMode($id)
    {
        $world = World::findOrFail($id);
        // Toggle Safe Mode (stored in status or separate column? User asked for SAFE_MODE state)
        // Let's use status 'SAFE_MODE' if currently ACTIVE
        
        if ($world->status === 'SAFE_MODE') {
            $world->update(['status' => 'ACTIVE']);
            \App\Models\GovernanceAuditLog::logAction(
                $world->id,
                'SAFE_MODE_DEACTIVATED',
                ['world' => $world->name],
                'INFO'
            );
            return back()->with('success', 'Safe Mode Deactivated.');
        } else {
            $world->update(['status' => 'SAFE_MODE']);
            \App\Models\GovernanceAuditLog::logAction(
                $world->id,
                'SAFE_MODE_ACTIVATED',
                ['world' => $world->name],
                'WARNING'
            );
            return back()->with('success', 'Safe Mode Activated. AI and Mutations disabled.');
        }
    }

    public function halt($id)
    {
        $world = World::findOrFail($id);
        $world->update([
            'status' => 'LOCKED',
            'health_status' => \App\Domains\World\Enums\WorldHealthStatus::HALTED
        ]);
        
        \App\Models\WorldAlert::create([
            'world_id' => $world->id,
            'type' => 'MANUAL_HALT',
            'severity' => 'CRITICAL',
            'message' => 'World halted by Operator Kill Switch.',
            'resolved' => false
        ]);

        // Audit Log - Article III: Kill Switch
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'KILL_SWITCH',
            [
                'world' => $world->name,
                'article' => 'Constitution Article III',
            ],
            'CRITICAL'
        );

        return back()->with('warning', 'EMERGENCY STOP EXECUTED. World Halted.');
    }
    public function editLaws($id)
    {
        $world = World::findOrFail($id);
        return view('admin.wmcp.worlds.edit_laws', compact('world'));
    }

    public function updateLaws(Request $request, $id)
    {
        $world = World::findOrFail($id);
        
        $validated = $request->validate([
            'magic_system' => 'required|string',
            'power_ceiling' => 'required|string',
            'tech_level' => 'required|string',
            'cultivation_allowed' => 'boolean',
            'myth_emergence' => 'boolean',
        ]);

        $currentProfile = $world->law_profile;
        
        $newProfile = new \App\Domains\World\ValueObjects\WorldLawProfile(
            magicSystem: \App\Domains\World\Enums\MagicSystemType::from($validated['magic_system']),
            powerCeiling: \App\Domains\World\Enums\PowerCeiling::from($validated['power_ceiling']),
            techLevel: \App\Domains\World\Enums\TechLevel::from($validated['tech_level']),
            cultivationAllowed: $request->has('cultivation_allowed'),
            mythEmergenceEnabled: $request->has('myth_emergence'),
            beliefToRealityRatio: $currentProfile->beliefToRealityRatio,
            heavenlyWayStrength: $currentProfile->heavenlyWayStrength
        );

        $world->law_profile = $newProfile;
        $world->save();

        return redirect()->route('admin.wmcp.worlds.show', $world)->with('success', 'World Laws updated successfully.');
    }
}
