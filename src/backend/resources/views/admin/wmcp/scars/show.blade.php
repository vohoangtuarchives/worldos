@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">⚡ Scar #{{ $scar->id }}</h1>
    <a href="{{ route('admin.wmcp.scars.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">⚡ Scar Details (IMMUTABLE)</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">World:</th>
                        <td>
                            <a href="{{ route('admin.wmcp.worlds.show', $scar->world_id) }}">
                                {{ $scar->world->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Source Event:</th>
                        <td>
                            @if($scar->sourceEvent)
                                <code>{{ $scar->sourceEvent->type }}</code><br>
                                <small class="text-muted">{{ $scar->sourceEvent->description ?? 'No description' }}</small>
                            @else
                                <span class="text-muted">Event no longer exists</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Weight:</th>
                        <td>
                            <span class="badge bg-{{ $scar->weight >= 7 ? 'danger' : ($scar->weight >= 4 ? 'warning' : 'secondary') }} fs-5">
                                {{ $scar->weight }}/10
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $scar->created_at->format('Y-m-d H:i') }} ({{ $scar->created_at->diffForHumans() }})</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($scar->sourceEvent)
            <div class="card">
                <div class="card-header">📜 Source Event Details</div>
                <div class="card-body">
                    <p><strong>Type:</strong> {{ $scar->sourceEvent->type }}</p>
                    <p><strong>Description:</strong> {{ $scar->sourceEvent->description ?? 'N/A' }}</p>
                    <p><strong>Occurred:</strong> {{ $scar->sourceEvent->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="alert alert-danger">
            <strong>🔒 IMMUTABLE</strong><br>
            This scar is a permanent consequence. It cannot be healed, edited, or deleted.
        </div>

        <div class="alert alert-info">
            <strong>📌 Governance:</strong><br>
            Scars enforce <u>permanent consequences</u> for world events. History cannot be rewritten.
        </div>
    </div>
</div>
@endsection
