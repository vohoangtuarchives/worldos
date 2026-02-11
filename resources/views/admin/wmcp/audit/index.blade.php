@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">📜 Governance Audit Log</h1>
        <small class="text-muted">Full audit trail of operator actions</small>
    </div>
</div>

<div class="alert alert-info mb-3">
    <strong>⚖️ Constitution Compliance:</strong> All governance actions are logged automatically per Article IV.
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.wmcp.audit.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">World</label>
                    <select name="world_id" class="form-select form-select-sm">
                        <option value="">All Worlds</option>
                        @foreach($worlds as $w)
                            <option value="{{ $w->id }}" {{ request('world_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action Type</label>
                    <select name="action_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="INFO" {{ request('severity') == 'INFO' ? 'selected' : '' }}>ℹ️ INFO</option>
                        <option value="WARNING" {{ request('severity') == 'WARNING' ? 'selected' : '' }}>⚠️ WARNING</option>
                        <option value="CRITICAL" {{ request('severity') == 'CRITICAL' ? 'selected' : '' }}>🔴 CRITICAL</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>World</th>
                        <th>Operator</th>
                        <th>Severity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><code class="small">{{ $log->action_type }}</code></td>
                        <td>
                            @if($log->world)
                                <a href="{{ route('admin.wmcp.worlds.show', $log->world_id) }}">
                                    {{ $log->world->name }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="small">{{ $log->operator }}</td>
                        <td>
                            @if($log->severity === 'CRITICAL')
                                <span class="badge bg-danger">🔴 CRITICAL</span>
                            @elseif($log->severity === 'WARNING')
                                <span class="badge bg-warning">⚠️ WARNING</span>
                            @else
                                <span class="badge bg-info">ℹ️ INFO</span>
                            @endif
                        </td>
                        <td>
                            @if($log->metadata)
                                <button class="btn btn-sm btn-outline-secondary" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#metadata-{{ $log->id }}">
                                    View
                                </button>
                                <div class="collapse mt-2" id="metadata-{{ $log->id }}">
                                    <pre class="bg-light p-2 rounded small mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No audit logs found. Try adjusting filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>Read-Only:</strong> Audit logs cannot be modified. They are immutable for compliance.
</div>
@endsection
