@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Incident Reports</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Incident ID</th>
                <th>World</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Summary</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->incident_id }}</td>
                <td>{{ $incident->world->name }}</td>
                <td>
                    <span class="badge bg-{{ $incident->severity === 'CRITICAL' ? 'danger' : ($incident->severity === 'HIGH' ? 'warning' : 'info') }}">
                        {{ $incident->severity }}
                    </span>
                </td>
                <td>{{ $incident->status }}</td>
                <td>{{ Str::limit($incident->summary, 50) }}</td>
                <td>{{ $incident->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <a href="{{ route('admin.wmcp.incidents.show', $incident) }}" class="btn btn-sm btn-outline-primary">View Report</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">No incidents recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
