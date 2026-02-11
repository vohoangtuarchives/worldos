@extends('layouts.admin')


@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Operator Dashboard</h1>
</div>

<!-- 🔴 CRITICAL WORLDS -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white fw-bold">
                🔴 CRITICAL WORLDS ({{ $criticalWorlds->count() }}) - IMMEDIATE ACTION REQUIRED
            </div>
            <div class="card-body p-2">
                @if($criticalWorlds->isEmpty())
                    <p class="text-muted m-2">No critical worlds.</p>
                @else
                    <div class="row">
                        @foreach($criticalWorlds as $world)
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.wmcp.worlds.show', $world) }}" class="text-decoration-none">
                                    <div class="card h-100 border-danger shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title text-danger fw-bold">{{ $world->name }}</h5>
                                            <p class="card-text small text-muted">ID: {{ $world->id }}</p>
                                            <span class="badge bg-danger">{{ $world->status }}</span>
                                            
                                            @if($world->events->isNotEmpty())
                                                <hr>
                                                <small class="text-danger fw-bold">{{ $world->events->first()->type ?? 'Unknown Alert' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 🟡 DEGRADED WORLDS -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark fw-bold">
                🟡 DEGRADED WORLDS ({{ $degradedWorlds->count() }}) - MONITOR CLOSELY
            </div>
            <div class="card-body p-2">
                @if($degradedWorlds->isEmpty())
                    <p class="text-muted m-2">No degraded worlds.</p>
                @else
                    <div class="row">
                        @foreach($degradedWorlds as $world)
                           <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.wmcp.worlds.show', $world) }}" class="text-decoration-none">
                                    <div class="card h-100 border-warning shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title text-dark">{{ $world->name }}</h5>
                                            <p class="card-text small text-muted">ID: {{ $world->id }}</p>
                                            <span class="badge bg-warning text-dark">{{ $world->status }}</span>
                                             @if($world->events->isNotEmpty())
                                                <hr>
                                                <small class="text-muted">{{ $world->events->first()->type ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ⚫ HALTED WORLDS (Post-Mortem) -->
@if($haltedWorlds->isNotEmpty())
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-dark bg-light">
            <div class="card-header bg-dark text-white fw-bold">
                ⚫ HALTED WORLDS ({{ $haltedWorlds->count() }}) - POST-MORTEM REQUIRED
            </div>
            <div class="card-body p-2">
                <div class="row">
                    @foreach($haltedWorlds as $world)
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.wmcp.worlds.show', $world) }}" class="text-decoration-none">
                                <div class="card h-100 bg-secondary text-white border-dark">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $world->name }}</h5>
                                        <p class="card-text small">ID: {{ $world->id }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 🟢 STABLE WORLDS -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white fw-bold">
                🟢 STABLE WORLDS ({{ $stableWorlds->count() }}) - NORMAL OPERATION
            </div>
            <div class="card-body p-2">
                @if($stableWorlds->isEmpty())
                    <p class="text-muted m-2">No stable worlds.</p>
                @else
                    <div class="row">
                        @foreach($stableWorlds as $world)
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.wmcp.worlds.show', $world) }}" class="text-decoration-none">
                                    <div class="card h-100 border-success shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title text-success">{{ $world->name }}</h5>
                                            <p class="card-text small text-muted">ID: {{ $world->id }}</p>
                                            <span class="badge bg-success">{{ $world->status }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 🔔 ACTIVE ALERTS -->
<div class="card mb-4">
    <div class="card-header bg-info text-white fw-bold">
        🔔 ACTIVE ALERTS ({{ $activeAlerts->count() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Severity</th>
                        <th>Type</th>
                        <th>World</th>
                        <th>Message</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeAlerts as $alert)
                    <tr>
                        <td><span class="badge bg-{{ $alert->severity === 'CRITICAL' ? 'danger' : 'warning' }}">{{ $alert->severity }}</span></td>
                        <td class="fw-bold">{{ $alert->type }}</td>
                        <td><a href="{{ route('admin.wmcp.worlds.show', $alert->world) }}">{{ $alert->world->name }}</a></td>
                        <td>{{ $alert->message }}</td>
                        <td>{{ $alert->created_at->diffForHumans() }}</td>
                        <td><button class="btn btn-xs btn-outline-success">Resolve</button></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-3">No active alerts. System is quiet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
