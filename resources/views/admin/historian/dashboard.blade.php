@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">📜 Historian Research Platform</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export Data</button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-dark mb-3">
            <div class="card-header">Sagas Analyzed</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ $stats['completed'] }} / {{ $stats['total'] }}</h5>
                <p class="card-text">Completed simulations vs Total attempts.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Worlds Simulated</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ $stats['worlds_simulated'] }}</h5>
                <p class="card-text">Total worlds generated across all sagas.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Global Patterns</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ count($globalPatterns['archetype_patterns']['most_frequent'] ?? []) }}</h5>
                <p class="card-text">Recurring archetype patterns detected.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                🚀 Recent Sagas
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Worlds</th>
                            <th scope="col">Created</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSagas as $saga)
                        <tr>
                            <td>{{ substr($saga->id, 0, 8) }}</td>
                            <td>{{ $saga->name }}</td>
                            <td>
                                <span class="badge bg-{{ $saga->status === 'completed' ? 'success' : ($saga->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ $saga->status }}
                                </span>
                            </td>
                            <td>{{ $saga->world_count }}</td>
                            <td>{{ $saga->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.historian.sagas.show', $saga) }}" class="btn btn-sm btn-outline-primary">Analyze</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.historian.sagas.index') }}" class="btn btn-link">View All Sagas</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                🧠 Pattern Insights
            </div>
            <div class="list-group list-group-flush">
                @forelse($globalPatterns['archetype_patterns']['patterns'] ?? [] as $pattern)
                    <div class="list-group-item">
                        <small class="text-muted">Archetype</small>
                        <p class="mb-1">{{ $pattern }}</p>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No global patterns detected yet.</div>
                @endforelse
                
                @forelse($globalPatterns['collapse_patterns']['collapse_triggers'] ?? [] as $archetype => $count)
                    <div class="list-group-item">
                        <small class="text-danger">Collapse Risk</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $archetype }}</span>
                            <span class="badge bg-danger rounded-pill">{{ $count }}</span>
                        </div>
                    </div>
                    @if($loop->iteration >= 3) @break @endif
                @empty
                    <!-- No collapse data -->
                @endforelse
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.historian.patterns.index') }}" class="btn btn-link">Deep Analysis</a>
            </div>
        </div>
    </div>
</div>
@endsection
