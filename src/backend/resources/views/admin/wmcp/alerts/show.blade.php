@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Alert #{{ $alert->id }}</h1>
        <small class="text-muted">
            @if($alert->resolved)
                <span class="badge bg-success">✅ Resolved</span>
            @else
                <span class="badge bg-danger">🔴 Active</span>
            @endif
        </small>
    </div>
    <div>
        <a href="{{ route('admin.wmcp.alerts.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Metadata Card -->
        <div class="card mb-3">
            <div class="card-header">Alert Metadata</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>World:</strong>
                    @if($alert->world)
                        <a href="{{ route('admin.wmcp.worlds.show', $alert->world_id) }}">
                            {{ $alert->world->name }}
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </li>
                <li class="list-group-item">
                    <strong>Type:</strong><br>
                    <code class="small">{{ $alert->type }}</code>
                </li>
                <li class="list-group-item">
                    <strong>Severity:</strong><br>
                    @if($alert->severity === 'CRITICAL')
                        <span class="badge bg-danger">🔴 CRITICAL</span>
                    @elseif($alert->severity === 'WARNING')
                        <span class="badge bg-warning">⚠️ WARNING</span>
                    @else
                        <span class="badge bg-info">ℹ️ INFO</span>
                    @endif
                </li>
                <li class="list-group-item">
                    <strong>Created:</strong><br>
                    <small>{{ $alert->created_at }}</small>
                </li>
                @if($alert->resolved)
                <li class="list-group-item">
                    <strong>Resolved At:</strong><br>
                    <small>{{ $alert->resolved_at }}</small>
                </li>
                <li class="list-group-item">
                    <strong>Resolved By:</strong><br>
                    <small>{{ $alert->resolved_by }}</small>
                </li>
                @endif
            </ul>
        </div>

        <!-- Resolve Card -->
        @if(!$alert->resolved)
        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <strong>✅ Resolve Alert</strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <strong>Article IV:</strong> Resolution requires justification.
                </p>
                <form action="{{ route('admin.wmcp.alerts.resolve', $alert->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label"><strong>Resolution Notes (Required)</strong></label>
                        <textarea name="resolution_notes" class="form-control" rows="4" 
                                  placeholder="Describe how this alert was resolved and what actions were taken..." 
                                  required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Mark as Resolved</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- Message Card -->
        <div class="card mb-3 border-{{ $alert->severity === 'CRITICAL' ? 'danger' : ($alert->severity === 'WARNING' ? 'warning' : 'info') }}">
            <div class="card-header bg-{{ $alert->severity === 'CRITICAL' ? 'danger' : ($alert->severity === 'WARNING' ? 'warning' : 'info') }} bg-opacity-10">
                Alert Message
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $alert->message }}</p>
            </div>
        </div>

        <!-- Details Card -->
        @if($alert->details)
        <div class="card mb-3">
            <div class="card-header">Technical Details</div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="max-height: 400px; overflow-y: auto;">{{ json_encode($alert->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif

        <!-- Resolution Notes -->
        @if($alert->resolved && $alert->resolution_notes)
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10">
                <strong>✅ Resolution Notes</strong>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Resolved by:</strong> {{ $alert->resolved_by }}</p>
                <p class="mb-2"><strong>Resolution Date:</strong> {{ $alert->resolved_at }}</p>
                <hr>
                <p class="mb-0">{{ $alert->resolution_notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="alert alert-info mt-3">
    <strong>Article IV:</strong> All incidents must be logged. No resume without post-mortem for CRITICAL alerts.
</div>
@endsection
