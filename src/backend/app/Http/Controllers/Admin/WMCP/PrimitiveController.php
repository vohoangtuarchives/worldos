<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\WorldPrimitive;
use App\Models\WFRVersion;
use App\Domains\World\Enums\PrimitiveDomain;
use Illuminate\Http\Request;

class PrimitiveController extends Controller
{
    /**
     * Primitive catalog (read-only)
     */
    public function index(Request $request)
    {
        $domain = $request->get('domain');
        $version = $request->get('version', WFRVersion::latestStable() ?? '1.0.0');

        $query = WorldPrimitive::where('version', $version)
            ->where('is_active', true);

        if ($domain) {
            $query->where('domain', $domain);
        }

        $primitives = $query->orderBy('domain')->orderBy('code')->get();
        $domains = PrimitiveDomain::cases();
        $versions = WFRVersion::orderByDesc('released_at')->get();

        return view('admin.wmcp.primitives.index', compact('primitives', 'domains', 'versions', 'domain', 'version'));
    }

    /**
     * Primitive detail (read-only)
     */
    public function show($id)
    {
        $primitive = WorldPrimitive::findOrFail($id);
        
        return view('admin.wmcp.primitives.show', compact('primitive'));
    }

    /**
     * Proposal form
     */
    public function propose()
    {
        $domains = PrimitiveDomain::cases();
        
        return view('admin.wmcp.primitives.propose', compact('domains'));
    }

    /**
     * Submit proposal (logged only, not executed)
     */
    public function submitProposal(Request $request)
    {
        $validated = $request->validate([
            'proposed_code' => 'required|string|max:100',
            'domain' => 'required|string',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'justification' => 'required|string',
            'power_analysis' => 'nullable|string',
        ]);

        // Log proposal (governance process - not auto-approved)
        \Log::info('WFR Primitive Proposal', [
            'operator' => auth()->user()->email ?? 'system',
            'proposed_code' => $validated['proposed_code'],
            'domain' => $validated['domain'],
            'justification' => $validated['justification'],
        ]);

        // Audit log
        \App\Models\GovernanceAuditLog::logAction(
            null, // No world_id
            'PRIMITIVE_PROPOSED',
            [
                'proposed_code' => $validated['proposed_code'],
                'domain' => $validated['domain'],
                'operator' => auth()->user()->email ?? 'system',
            ],
            'INFO'
        );

        return redirect()->route('admin.wmcp.primitives.index')
            ->with('success', 'Primitive proposal submitted for review. Requires ADR approval before merging to WFR.');
    }
}
