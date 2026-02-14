@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🌍 {{ $world->name }} - World Factors</h1>
        <small class="text-muted">Comprehensive overview of all factors affecting world operation</small>
    </div>
    <a href="{{ route('admin.wmcp.worlds.show', $world->id) }}" class="btn btn-sm btn-secondary">← Back to World</a>
</div>

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Seeds</h5>
                <h2>{{ $stats['active_seeds'] }}/{{ $stats['total_seeds'] }}</h2>
                <small class="text-muted">Active / Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Myths</h5>
                <h2>{{ $stats['active_myths'] }}/{{ $stats['total_myths'] }}</h2>
                <small class="text-muted">Active / Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Scars</h5>
                <h2>{{ $stats['total_scars'] }}</h2>
                <small class="text-muted">Permanent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-{{ $stats['unresolved_alerts'] > 0 ? 'warning' : 'secondary' }}">
            <div class="card-body">
                <h5 class="text-muted">Alerts</h5>
                <h2>{{ $stats['unresolved_alerts'] }}</h2>
                <small class="text-muted">Unresolved</small>
            </div>
        </div>
    </div>
</div>

<!-- Foundation Layer -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary bg-opacity-10">
                <strong>🧱 Foundation Layer - Primitives (WFR)</strong>
            </div>
            <div class="card-body">
                @if($primitives->isEmpty())
                    <p class="text-muted">No primitives bound to this world yet.</p>
                @else
                    @foreach($primitives as $domain => $domainPrimitives)
                        <div class="mb-3">
                            <h6>{{ ucfirst($domain) }}</h6>
                            @foreach($domainPrimitives as $primitive)
                                <span class="badge bg-secondary me-1">{{ $primitive->code }}</span>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Input Layer -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success bg-opacity-10">
                <strong>🌱 Input Layer - Seeds ({{ $stats['total_seeds'] }})</strong>
                <a href="{{ route('admin.wmcp.seeds.active', $world->id) }}" class="float-end btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($seeds as $seed)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <strong>{{ $seed->template->name }}</strong><br>
                            <small class="text-muted">{{ $seed->template->type }} - {{ $seed->template->dimension }}</small>
                        </div>
                        <span class="badge bg-{{ $seed->state->color() }}">{{ $seed->state->label() }}</span>
                    </div>
                @empty
                    <p class="text-muted">No seeds injected.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning bg-opacity-10">
                <strong>🚨 Active Alerts ({{ $stats['unresolved_alerts'] }})</strong>
                <a href="{{ route('admin.wmcp.alerts.index') }}?world_id={{ $world->id }}" class="float-end btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($alerts as $alert)
                    <div class="alert alert-{{ $alert->severity === 'CRITICAL' ? 'danger' : ($alert->severity === 'WARNING' ? 'warning' : 'info') }} mb-2 py-2">
                        <strong>{{ $alert->type }}</strong><br>
                        <small>{{ $alert->message }}</small>
                    </div>
                @empty
                    <p class="text-muted">No active alerts.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Emergent Layer -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info bg-opacity-10">
                <strong>🧠 Emergent Layer - Myths ({{ $stats['total_myths'] }})</strong>
                <a href="{{ route('admin.wmcp.myths.index') }}?world_id={{ $world->id }}" class="float-end btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($myths as $myth)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <strong>{{ $myth->name }}</strong><br>
                            <small class="text-muted">Status: {{ ucfirst($myth->status) }}</small>
                        </div>
                        <span class="badge bg-primary">Strength: {{ $myth->strength }}</span>
                    </div>
                @empty
                    <p class="text-muted">No myths emerged.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger bg-opacity-10">
                <strong>⚡ Consequences - Scars ({{ $stats['total_scars'] }})</strong>
                <a href="{{ route('admin.wmcp.scars.index') }}?world_id={{ $world->id }}" class="float-end btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($scars as $scar)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            @if($scar->sourceEvent)
                                <strong>{{ Str::limit($scar->sourceEvent->type, 30) }}</strong>
                            @else
                                <span class="text-muted">Unknown event</span>
                            @endif
                            <br>
                            <small class="text-muted">{{ $scar->created_at->diffForHumans() }}</small>
                        </div>
                        <span class="badge bg-{{ $scar->weight >= 7 ? 'danger' : 'warning' }}">{{ $scar->weight }}/10</span>
                    </div>
                @empty
                    <p class="text-muted">No scars recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Timeline -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary bg-opacity-10">
                <strong>📜 Recent Events Timeline ({{ $stats['total_events'] }} total)</strong>
                <a href="{{ route('admin.wmcp.events.index') }}?world_id={{ $world->id }}" class="float-end btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($recentEvents as $event)
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $event->type }}</strong>
                            <small class="text-muted">{{ $event->created_at->diffForHumans() }}</small>
                        </div>
                        @if($event->description)
                            <small class="text-muted">{{ Str::limit($event->description, 100) }}</small>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">No events recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <strong>📊 World Factors Overview:</strong> This dashboard shows all factors affecting <code>{{ $world->name }}</code> operation:
    Foundation (Primitives) → Input (Seeds) → Emergent (Myths) → Consequences (Scars) + Monitoring (Events, Alerts)
</div>
@endsection
