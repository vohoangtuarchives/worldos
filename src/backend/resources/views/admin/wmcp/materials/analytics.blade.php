@extends('layouts.admin')

@section('title', 'Material Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Material Analytics Dashboard</h2>
            
            {{-- World Selector --}}
            <form method="GET" action="{{ route('admin.materials.analytics') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <label for="world_id" class="form-label">Select World</label>
                        <select name="world_id" id="world_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose a World --</option>
                            @foreach(\App\Models\World::all() as $w)
                                <option value="{{ $w->id }}" {{ $world && $world->id === $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} (Tick: {{ $w->tick }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($world && $analytics)
        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">Total Activations</h6>
                        <h3>{{ $analytics['activation_rates']['total_activations'] }}</h3>
                        <small>Avg: {{ number_format($analytics['activation_rates']['average_per_epoch'], 2) }}/epoch</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">Total Mutations</h6>
                        <h3>{{ $analytics['mutation_chains']['total_mutations'] }}</h3>
                        <small>{{ $analytics['mutation_chains']['unique_sources'] }} unique sources</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">Active Conflicts</h6>
                        <h3>{{ $analytics['conflict_patterns']['conflict_count'] }}</h3>
                        <small>Density: {{ number_format($analytics['conflict_patterns']['conflict_density'], 2) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">Active Materials</h6>
                        <h3>{{ $analytics['lifecycle_breakdown']['active'] }}</h3>
                        <small>{{ $analytics['lifecycle_breakdown']['dormant'] }} dormant</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lifecycle Breakdown --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Lifecycle Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lifecycleChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Material Distribution --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Distribution by Ontology</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="ontologyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activation Rates Over Time --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Activation Rates Over Time</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="activationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Materials --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Top Materials by Strength</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Strength</th>
                                    <th>Ontology</th>
                                    <th>Function</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['top_materials'] as $material)
                                    <tr>
                                        <td><code>{{ $material['code'] }}</code></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $material['strength'] * 10 }}%">
                                                    {{ $material['strength'] }}
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info">{{ $material['ontology'] }}</span></td>
                                        <td><span class="badge bg-secondary">{{ $material['function'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Active Conflicts --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Active Conflicts</h5>
                    </div>
                    <div class="card-body">
                        @if(count($analytics['conflict_patterns']['active_conflicts']) > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Material 1</th>
                                        <th>Material 2</th>
                                        <th>Intensity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['conflict_patterns']['active_conflicts'] as $conflict)
                                        <tr>
                                            <td><code>{{ $conflict['material1'] }}</code></td>
                                            <td><code>{{ $conflict['material2'] }}</code></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ $conflict['conflict_intensity'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No active conflicts detected.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Mutation Chains --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Mutation Chains</h5>
                    </div>
                    <div class="card-body">
                        @if(count($analytics['mutation_chains']['mutations']) > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Epoch</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['mutation_chains']['mutations'] as $mutation)
                                        <tr>
                                            <td><code>{{ $mutation['from'] }}</code></td>
                                            <td><code>{{ $mutation['to'] }}</code></td>
                                            <td>{{ $mutation['epoch'] }}</td>
                                            <td><small>{{ $mutation['description'] }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No mutations have occurred yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart.js Scripts --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Lifecycle Chart
            new Chart(document.getElementById('lifecycleChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Dormant', 'Active', 'Retired'],
                    datasets: [{
                        data: [
                            {{ $analytics['lifecycle_breakdown']['dormant'] }},
                            {{ $analytics['lifecycle_breakdown']['active'] }},
                            {{ $analytics['lifecycle_breakdown']['retired'] }}
                        ],
                        backgroundColor: ['#6c757d', '#28a745', '#dc3545']
                    }]
                }
            });

            // Ontology Chart
            new Chart(document.getElementById('ontologyChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($analytics['material_distribution']['by_ontology'])) !!},
                    datasets: [{
                        label: 'Count',
                        data: {!! json_encode(array_values($analytics['material_distribution']['by_ontology'])) !!},
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Activation Chart
            new Chart(document.getElementById('activationChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($analytics['activation_rates']['by_epoch'])) !!},
                    datasets: [{
                        label: 'Activations',
                        data: {!! json_encode(array_values($analytics['activation_rates']['by_epoch'])) !!},
                        borderColor: '#28a745',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    @else
        <div class="alert alert-info">
            Please select a world to view analytics.
        </div>
    @endif
</div>
@endsection
