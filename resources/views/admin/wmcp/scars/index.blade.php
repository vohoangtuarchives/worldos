@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">⚡ World Scars</h1>
        <small class="text-muted">Permanent consequences - IMMUTABLE</small>
    </div>
</div>

<div class="alert alert-danger mb-4">
    <strong>⚠️ Scar System:</strong> Scars are <u>permanent consequences</u> of world events. They <strong>CANNOT</strong> be healed, edited, or deleted.
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Filter by World</label>
                <select name="world_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Worlds</option>
                    @foreach($worlds as $world)
                        <option value="{{ $world->id }}" {{ $worldId == $world->id ? 'selected' : '' }}>
                            {{ $world->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Scars Table -->
<div class="card">
    <div class="card-body">
        @if($scars->isEmpty())
            <p class="text-muted text-center">No scars recorded yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>World</th>
                            <th>Source Event</th>
                            <th>Weight</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scars as $scar)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.wmcp.worlds.show', $scar->world_id) }}">
                                        {{ $scar->world->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($scar->sourceEvent)
                                        <code>{{ Str::limit($scar->sourceEvent->type, 30) }}</code>
                                    @else
                                        <span class="text-muted">Event deleted</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $scar->weight >= 7 ? 'danger' : ($scar->weight >= 4 ? 'warning' : 'secondary') }}">
                                        {{ $scar->weight }}/10
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $scar->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.wmcp.scars.show', $scar->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $scars->links() }}
            </div>
        @endif
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>🔒 IMMUTABLE:</strong> Scars cannot be healed or forgotten. History is permanent.
</div>
@endsection
