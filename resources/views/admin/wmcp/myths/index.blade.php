@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🧠 World Myths</h1>
        <small class="text-muted">Emergent mythology from collective beliefs</small>
    </div>
</div>

<div class="alert alert-info mb-4">
    <strong>Myth System:</strong> Myths emerge when beliefs reach critical mass. Semi-mutable (can decay/merge, but not manually created).
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

<!-- Myths Table -->
<div class="card">
    <div class="card-body">
        @if($myths->isEmpty())
            <p class="text-muted text-center">No myths emerged yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>World</th>
                            <th>Myth Name</th>
                            <th>Status</th>
                            <th>Strength</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myths as $myth)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.wmcp.worlds.show', $myth->world_id) }}">
                                        {{ $myth->world->name }}
                                    </a>
                                </td>
                                <td><strong>{{ $myth->name }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $myth->status === 'active' ? 'success' : ($myth->status === 'decaying' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($myth->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $myth->strength }}</span>
                                </td>
                                <td class="small text-muted">{{ $myth->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.wmcp.myths.show', $myth->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $myths->links() }}
            </div>
        @endif
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>🔒 Semi-Mutable:</strong> Myths can decay or merge naturally, but cannot be manually created or deleted.
</div>
@endsection
