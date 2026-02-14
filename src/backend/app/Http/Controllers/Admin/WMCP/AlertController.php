<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\WorldAlert;
use App\Models\World;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = WorldAlert::with('world:id,name')
            ->orderBy('created_at', 'desc');

        // Filter by World
        if ($request->filled('world_id')) {
            $query->where('world_id', $request->world_id);
        }

        // Filter by Severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by Status (resolved/unresolved)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('resolved', false);
            } elseif ($request->status === 'resolved') {
                $query->where('resolved', true);
            }
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $alerts = $query->paginate(30);
        $worlds = World::orderBy('name')->get(['id', 'name']);

        // Get distinct types and severities for filters
        $types = WorldAlert::select('type')->distinct()->pluck('type');
        $severities = ['INFO', 'WARNING', 'CRITICAL'];

        return view('admin.wmcp.alerts.index', compact('alerts', 'worlds', 'types', 'severities'));
    }

    public function show($id)
    {
        $alert = WorldAlert::with('world')->findOrFail($id);
        return view('admin.wmcp.alerts.show', compact('alert'));
    }

    public function resolve(Request $request, $id)
    {
        $alert = WorldAlert::findOrFail($id);

        $validated = $request->validate([
            'resolution_notes' => 'required|string|min:10',
        ]);

        $alert->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->user()->email ?? 'system',
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        // Audit Log
        \App\Models\GovernanceAuditLog::logAction(
            $alert->world_id,
            'ALERT_RESOLVED',
            [
                'alert_type' => $alert->type,
                'alert_severity' => $alert->severity,
                'resolution_notes' => $validated['resolution_notes'],
            ],
            'INFO'
        );

        return redirect()->route('admin.wmcp.alerts.index')
            ->with('success', 'Alert resolved successfully.');
    }
}
