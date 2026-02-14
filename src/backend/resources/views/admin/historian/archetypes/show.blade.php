@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">🧬 Archetype: {{ $archetype['key'] }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.historian.archetypes.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-dark text-white mb-3">
            <div class="card-header">Domain & Polarity</div>
            <div class="card-body">
                <h5 class="card-title">{{ ucfirst($archetype['domain']) }}</h5>
                <p class="card-text">
                    @foreach($archetype['polarity'] as $pole)
                        <span class="badge bg-secondary">{{ $pole }}</span>
                    @endforeach
                </p>
                <div class="d-flex justify-content-between text-muted small mt-3">
                    <span>Baseline Weight: {{ $archetype['baseline_weight'] }}</span>
                    <span>Volatility: {{ $archetype['volatility'] }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center mb-3">
                    <div class="card-header">Appearance Rate</div>
                    <div class="card-body">
                        <h3>{{ number_format($analytics['appearance_rate'] * 100, 1) }}%</h3>
                        <small class="text-muted">Presence in Sagas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center mb-3">
                    <div class="card-header">Dominance Rate</div>
                    <div class="card-body">
                        <h3 class="text-success">{{ number_format($analytics['dominance_rate'] * 100, 1) }}%</h3>
                        <small class="text-muted">High Intensity Frequency</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center mb-3">
                    <div class="card-header">Collapse Role</div>
                    <div class="card-body">
                        <h3 class="text-danger">{{ number_format($analytics['collapse_rate'] * 100, 1) }}%</h3>
                        <small class="text-muted">Involvement in Collapses</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Top Related Sagas</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Saga</th>
                            <th>Status</th>
                            <th>World Count</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($relatedSagas as $saga)
                        <tr>
                            <td>{{ $saga->name }}</td>
                            <td>
                                <span class="badge bg-{{ $saga->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ $saga->status }}
                                </span>
                            </td>
                            <td>{{ $saga->world_count }}</td>
                            <td>{{ $saga->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.historian.sagas.show', $saga) }}" class="btn btn-sm btn-outline-primary">View Analysis</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted text-center">
                Showing recent sagas where this archetype appeared.
            </div>
        </div>
    </div>
</div>
@endsection
