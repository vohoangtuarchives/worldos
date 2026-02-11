@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Event #{{ $event->id }}</h1>
        <small class="text-muted">Timeline: <code>{{ $event->timeline_id }}</code></small>
    </div>
    <div>
        <a href="{{ route('admin.wmcp.events.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Metadata Card -->
        <div class="card mb-3">
            <div class="card-header">Metadata</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>World:</strong>
                    @if($event->world)
                        <a href="{{ route('admin.wmcp.worlds.show', $event->world_id) }}">
                            {{ $event->world->name }}
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </li>
                <li class="list-group-item">
                    <strong>Chapter:</strong> {{ $event->chapter ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                    <strong>Tick:</strong> {{ $event->tick ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                    <strong>Type:</strong> <br><code class="small">{{ $event->type }}</code>
                </li>
                <li class="list-group-item">
                    <strong>Created:</strong><br>
                    <small>{{ $event->created_at }}</small>
                </li>
            </ul>
        </div>

        <!-- Replay Card -->
        <div class="card border-primary">
            <div class="card-header bg-primary bg-opacity-10">
                <strong>🔄 Replay Engine</strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Replay this timeline up to this event to verify determinism.
                </p>
                <form action="{{ route('admin.wmcp.simulation.run', $event->world_id ?? 1) }}" method="POST">
                    @csrf
                    <input type="hidden" name="replay_timeline" value="{{ $event->timeline_id }}">
                    <input type="hidden" name="replay_until_chapter" value="{{ $event->chapter }}">
                    <button type="submit" class="btn btn-primary w-100" disabled title="Replay feature pending">
                        Replay to Chapter {{ $event->chapter }}
                    </button>
                </form>
                <small class="text-muted d-block mt-2">
                    Note: Full replay UI pending implementation
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Payload Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Event Payload</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyPayload()">📋 Copy JSON</button>
            </div>
            <div class="card-body">
                <pre id="payload" class="bg-light p-3 rounded" style="max-height: 600px; overflow-y: auto;"><code>{{ json_encode(is_array($event->payload) ? $event->payload : json_decode($event->payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            </div>
        </div>

        <!-- Context Info -->
        <div class="alert alert-info mt-3">
            <strong>ℹ️ Event Sourcing:</strong> Events are immutable. This payload represents the exact state change that occurred at Chapter {{ $event->chapter }}.
        </div>
    </div>
</div>

<script>
function copyPayload() {
    const payload = document.getElementById('payload').textContent;
    navigator.clipboard.writeText(payload).then(() => {
        alert('Payload copied to clipboard!');
    });
}
</script>
@endsection
