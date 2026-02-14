@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">📊 World Health History</h1>
        <small class="text-muted">Track health trends over time</small>
    </div>
</div>

<div class="alert alert-info mb-3">
    <strong>💡 Tip:</strong> Run <code>php artisan world:snapshot-health</code> periodically (e.g., hourly via cron) to build trend data.
</div>

<!-- Worlds Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>World</th>
                        <th>Current Health</th>
                        <th>Last Snapshot</th>
                        <th>Total Snapshots</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($worlds as $world)
                    <tr>
                        <td><strong>{{ $world['name'] }}</strong></td>
                        <td>
                            @php
                                $healthStatus = $world['current_health'];
                                $color = $healthStatus->color();
                            @endphp
                            <span class="badge bg-{{ $color }}">
                                {{ $healthStatus->value }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $world['last_snapshot'] ? $world['last_snapshot']->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $world['snapshot_count'] }}</span>
                        </td>
                        <td>
                            @if($world['snapshot_count'] > 0)
                                <a href="{{ route('admin.wmcp.health.show', $world['id']) }}" 
                                   class="btn btn-sm btn-outline-primary">View History</a>
                            @else
                                <span class="text-muted small">No data yet</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No worlds found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>Setup Cron Job:</strong> Add to <code>app/Console/Kernel.php</code>:
    <pre class="mb-0 mt-2 small">$schedule->command('world:snapshot-health')->hourly();</pre>
</div>
@endsection
