@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">🧠 Pattern Recognition</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export Patterns</button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Archetype Patterns -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                🧬 Archetype Patterns
            </div>
            <div class="card-body">
                <p class="card-text text-muted">Recurring archetype dominance across {{ $sagas->count() }} analyzed sagas.</p>
                
                <ul class="list-group list-group-flush">
                    @forelse($patterns['archetype_patterns']['patterns'] ?? [] as $pattern)
                        <li class="list-group-item">
                            <i class="bi bi-diagram-3 me-2"></i> {{ $pattern }}
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No global archetype patterns detected yet.</li>
                    @endforelse
                </ul>

                <hr>

                <h6>Most Frequent Archetypes</h6>
                @foreach($patterns['archetype_patterns']['most_frequent'] ?? [] as $arch => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $arch }}</span>
                        <span class="badge bg-secondary rounded-pill">{{ $count }} occurrences</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Collapse Patterns -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-danger text-white">
                💥 Collapse Patterns
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Collapses: <strong>{{ $patterns['collapse_patterns']['total_collapses'] ?? 0 }}</strong></span>
                    <span>Avg per Saga: <strong>{{ number_format($patterns['collapse_patterns']['avg_collapses_per_saga'] ?? 0, 1) }}</strong></span>
                </div>

                <h6>Common Collapse Triggers</h6>
                <ul class="list-group list-group-flush mb-3">
                    @forelse($patterns['collapse_patterns']['collapse_triggers'] ?? [] as $arch => $count)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $arch }}
                            <span class="badge bg-danger rounded-pill">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No collapse patterns detected yet.</li>
                    @endforelse
                </ul>

                <div class="alert alert-warning mt-3">
                    <small>High frequency triggers indicate structural instability in the simulator's law configuration.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Divergence Patterns -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                🌿 Divergence Patterns
            </div>
            <div class="card-body">
                <p class="card-text text-muted">Rare or unexpected events that deviated from the norm.</p>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Saga ID</th>
                                <th>Observation</th>
                                <th>Context</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patterns['divergence_patterns']['examples'] ?? [] as $divergence)
                                <tr>
                                    <td>{{ substr($divergence['saga_id'], 0, 8) }}</td>
                                    <td>{{ $divergence['observation'] }}</td>
                                    <td>
                                        <pre class="m-0 small">{{ json_encode($divergence['context'], JSON_PRETTY_PRINT) }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No significant divergences recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
