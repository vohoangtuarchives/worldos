@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">{{ $world->name }} - Health History</h1>
        <small class="text-muted">
            Current: 
            <span class="badge bg-{{ $world->health_status->color() }}">
                {{ $world->health_status->value }}
            </span>
        </small>
    </div>
    <div>
        <a href="{{ route('admin.wmcp.health.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<!-- Chart -->
<div class="card mb-3">
    <div class="card-header">Health Trend (Last 30 Days)</div>
    <div class="card-body">
        <canvas id="healthChart" height="80"></canvas>
    </div>
</div>

<!-- Snapshots Table -->
<div class="card">
    <div class="card-header">Snapshot History</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Recorded At</th>
                        <th>Health Status</th>
                        <th>Tick</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($snapshots as $snapshot)
                    <tr>
                        <td class="text-muted small">{{ $snapshot->recorded_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <span class="badge bg-{{ $snapshot->health_status->color() }}">
                                {{ $snapshot->health_status->value }}
                            </span>
                        </td>
                        <td><code>{{ $snapshot->tick ?? 'N/A' }}</code></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">
                            No snapshots recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('healthChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Health Score',
            data: @json($chartData['data']),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        if (value === 100) return 'STABLE';
                        if (value === 60) return 'DEGRADED';
                        if (value === 20) return 'CRITICAL';
                        if (value === 0) return 'HALTED';
                        return value;
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endpush
