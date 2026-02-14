<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\SeedTemplate;
use App\Models\World;
use Illuminate\Http\Request;

class SeedController extends Controller
{
    public function index()
    {
        $seeds = SeedTemplate::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.wmcp.seeds.index', compact('seeds'));
    }

    public function create()
    {
        $types = ['CONFLICT', 'DISCOVERY', 'TRAGEDY', 'BLESSING', 'MYSTERY', 'PROPHECY'];
        $dimensions = ['personal', 'family', 'faction', 'city', 'world'];
        
        return view('admin.wmcp.seeds.create', compact('types', 'dimensions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'dimension' => 'required|string',
            'severity' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $seed = SeedTemplate::create($validated);

        return redirect()->route('admin.wmcp.seeds.index')
            ->with('success', "Seed template '{$seed->name}' created successfully.");
    }

    public function edit($id)
    {
        $seed = SeedTemplate::findOrFail($id);
        $types = ['CONFLICT', 'DISCOVERY', 'TRAGEDY', 'BLESSING', 'MYSTERY', 'PROPHECY'];
        $dimensions = ['personal', 'family', 'faction', 'city', 'world'];
        
        return view('admin.wmcp.seeds.edit', compact('seed', 'types', 'dimensions'));
    }

    public function update(Request $request, $id)
    {
        $seed = SeedTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'dimension' => 'required|string',
            'severity' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $seed->update($validated);

        return redirect()->route('admin.wmcp.seeds.index')
            ->with('success', "Seed template '{$seed->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $seed = SeedTemplate::findOrFail($id);
        $seed->delete();

        return redirect()->route('admin.wmcp.seeds.index')
            ->with('success', 'Seed template deleted successfully.');
    }

    public function inject(Request $request, $templateId, $worldId)
    {
        $template = SeedTemplate::findOrFail($templateId);
        $world = World::findOrFail($worldId);

        // Governance Validation
        $validator = new \App\Domains\World\Services\SeedGovernanceValidator();
        
        if (!$validator->canInjectSeed($world, $template)) {
            $reason = $validator->getViolationReason($world, $template);
            
            // Create SEED_OVERLOAD alert
            \App\Models\WorldAlert::create([
                'world_id' => $world->id,
                'type' => 'SEED_OVERLOAD',
                'severity' => 'WARNING',
                'message' => "Seed injection blocked: {$reason}",
                'resolved' => false,
            ]);
            
            return back()->with('error', "Seed injection blocked: {$reason}");
        }

        // Create WorldSeed (DORMANT state)
        $worldSeed = \App\Models\WorldSeed::create([
            'world_id' => $world->id,
            'seed_template_id' => $template->id,
            'state' => \App\Domains\World\Enums\SeedState::DORMANT,
            'metadata' => [
                'injected_by' => auth()->user()->email ?? 'system',
                'injected_at_tick' => $world->clock->current_tick ?? 0,
            ],
        ]);

        // Create Alert
        \App\Models\WorldAlert::create([
            'world_id' => $world->id,
            'type' => 'SEED_INJECTED',
            'severity' => 'INFO',
            'message' => "Seed '{$template->name}' ({$template->type}, {$template->dimension}) injected (DORMANT).",
            'resolved' => false,
        ]);

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'SEED_INJECTED',
            [
                'seed_template' => $template->name,
                'type' => $template->type,
                'dimension' => $template->dimension,
                'severity' => $template->severity,
                'state' => 'DORMANT',
            ],
            'INFO'
        );

        return back()->with('success', "Seed '{$template->name}' injected successfully (DORMANT). Will activate during simulation.");
    }

    public function active($worldId)
    {
        $world = World::with('clock')->findOrFail($worldId);
        $seeds = \App\Models\WorldSeed::where('world_id', $worldId)
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.wmcp.seeds.active', compact('world', 'seeds'));
    }

    public function forceExhaust(Request $request, $worldId, $seedId)
    {
        $world = World::findOrFail($worldId);
        $seed = \App\Models\WorldSeed::findOrFail($seedId);

        if ($seed->world_id !== $world->id) {
            abort(404);
        }

        $seed->forceExhaust();

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $world->id,
            'SEED_FORCE_EXHAUSTED',
            [
                'seed_template' => $seed->template->name,
                'operator' => auth()->user()->email ?? 'system',
            ],
            'WARNING'
        );

        return back()->with('success', 'Seed force-exhausted successfully.');
    }
}
