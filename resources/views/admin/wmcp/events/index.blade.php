@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">📜 World Events</h1>
    <div>
        <a href="{{ route('admin.wmcp.events.export', request()->query()) }}" class="btn btn-sm btn-outline-primary">
            📥 Export JSON
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.wmcp.events.index') }}">
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
                <div class="col-md-3">
                    <label class="form-label">Event Type</label>
                    <input type="text" name="type" class="form-control form-control-sm" 
                           value="{{ request('type') }}" placeholder="e.g. FactionAction">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tick From</label>
                    <input type="number" name="tick_from" class="form-control form-control-sm" 
                           value="{{ request('tick_from') }}" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tick To</label>
                    <input type="number" name="tick_to" class="form-control form-control-sm" 
                           value="{{ request('tick_to') }}" placeholder="999">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Events Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Timeline</th>
                        <th>World</th>
                        <th>Chapter/Tick</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td class="text-muted small">{{ $event->id }}</td>
                        <td><code class="small">{{ Str::limit($event->timeline_id, 20) }}</code></td>
                        <td>
                            @if($event->world)
                                <a href="{{ route('admin.wmcp.worlds.show', $event->world_id) }}">
                                    {{ $event->world->name }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">Ch {{ $event->chapter ?? $event->tick }}</span>
                        </td>
                        <td><code class="small">{{ class_basename($event->type) }}</code></td>
                        <td class="text-muted small">{{ $event->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.wmcp.events.show', $event->id) }}" 
                               class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No events found. Try adjusting filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $events->links() }}
        </div>
    </div>
</div>

<div class="alert alert-info mt-3">
    <strong>💡 Tip:</strong> Events are the immutable history of your worlds. Use filters to debug specific timelines or chapters.
</div>
@endsection
