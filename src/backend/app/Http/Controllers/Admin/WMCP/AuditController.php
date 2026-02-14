<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Models\GovernanceAuditLog;
use App\Models\World;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = GovernanceAuditLog::with('world:id,name')
            ->orderBy('created_at', 'desc');

        // Filter by World
        if ($request->filled('world_id')) {
            $query->where('world_id', $request->world_id);
        }

        // Filter by Action Type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by Severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);
        $worlds = World::orderBy('name')->get(['id', 'name']);
        
        // Get distinct action types
        $actionTypes = GovernanceAuditLog::select('action_type')
            ->distinct()
            ->pluck('action_type');

        return view('admin.wmcp.audit.index', compact('logs', 'worlds', 'actionTypes'));
    }
}
