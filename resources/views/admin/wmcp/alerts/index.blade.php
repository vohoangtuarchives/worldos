@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🚨 World Alerts</h1>
        <small class="text-muted">Article IV: Mọi sự cố phải được ghi nhận</small>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.wmcp.alerts.index') }}">
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
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($severities as $sev)
                            <option value="{{ $sev }}" {{ request('severity') == $sev ? 'selected' : '' }}>
                                {{ $sev }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🔴 Active</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Alerts Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>World</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="{{ !$alert->resolved ? 'table-warning' : '' }}">
                        <td class="text-muted small">#{{ $alert->id }}</td>
                        <td>
                            @if($alert->world)
                                <a href="{{ route('admin.wmcp.worlds.show', $alert->world_id) }}">
                                    {{ $alert->world->name }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td><code class="small">{{ $alert->type }}</code></td>
                        <td>
                            @if($alert->severity === 'CRITICAL')
                                <span class="badge bg-danger">🔴 CRITICAL</span>
                            @elseif($alert->severity === 'WARNING')
                                <span class="badge bg-warning">⚠️ WARNING</span>
                            @else
                                <span class="badge bg-info">ℹ️ INFO</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($alert->message, 60) }}</td>
                        <td>
                            @if($alert->resolved)
                                <span class="badge bg-success">✅ Resolved</span>
                            @else
                                <span class="badge bg-danger">🔴 Active</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $alert->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.wmcp.alerts.show', $alert->id) }}" 
                               class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No alerts found. System is healthy! 🎉
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $alerts->links() }}
        </div>
    </div>
</div>

<div class="alert alert-info mt-3">
    <strong>💡 Operator Protocol:</strong> All CRITICAL alerts must be investigated. Resolution requires justification per Article IV.
</div>
@endsection
