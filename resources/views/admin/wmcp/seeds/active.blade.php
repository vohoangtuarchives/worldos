@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">🎯 Active Seeds - {{ $world->name }}</h1>
    <a href="{{ route('admin.wmcp.worlds.show', $world->id) }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="alert alert-info mb-4">
    <strong>Seed Governance:</strong> Seeds have lifecycle states (DORMANT → ACTIVE → EXHAUSTED). 
    Operators can delay activation or force exhaust.
</div>

@if($seeds->isEmpty())
    <div class="alert alert-secondary">
        No seeds injected into this world yet.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Template</th>
                    <th>Type</th>
                    <th>Dimension</th>
                    <th>Severity</th>
                    <th>State</th>
                    <th>Activation Tick</th>
                    <th>Exhaustion Tick</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seeds as $seed)
                    <tr>
                        <td><strong>{{ $seed->template->name }}</strong></td>
                        <td>
                            <span class="badge bg-primary">{{ $seed->template->type }}</span>
                        </td>
                        <td>{{ ucfirst($seed->template->dimension) }}</td>
                        <td>
                            <span class="badge bg-{{ $seed->template->severity >= 7 ? 'danger' : ($seed->template->severity >= 4 ? 'warning' : 'secondary') }}">
                                {{ $seed->template->severity }}/10
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $seed->state->color() }}">
                                {{ $seed->state->label() }}
                            </span>
                        </td>
                        <td>{{ $seed->activation_tick ?? '—' }}</td>
                        <td>{{ $seed->exhaustion_tick ?? '—' }}</td>
                        <td>
                            @if($seed->state === \App\Domains\World\Enums\SeedState::ACTIVE)
                                <form method="POST" action="{{ route('admin.wmcp.seeds.force_exhaust', [$world->id, $seed->id]) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" 
                                            onclick="return confirm('Force exhaust this seed?')">
                                        ⏹️ Exhaust
                                    </button>
                                </form>
                            @endif
                            
                            @if($seed->state === \App\Domains\World\Enums\SeedState::DORMANT)
                                <span class="text-muted small">Waiting for activation...</span>
                            @endif
                            
                            @if($seed->state === \App\Domains\World\Enums\SeedState::EXHAUSTED)
                                <span class="text-muted small">Archived</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
