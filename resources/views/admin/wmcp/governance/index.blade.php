@extends('layouts.admin')

@section('content')
<h2>AI Governance Logs</h2>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>World ID</th>
                <th>Status</th>
                <th>System Prompt</th>
                <th>Violations</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->world_id }}</td>
                <td>
                    <span class="badge bg-{{ $log->status === 'ACCEPTED' ? 'success' : ($log->status === 'FAILED' ? 'dark' : 'danger') }}">
                        {{ $log->status }}
                    </span>
                </td>
                <td>{{ Str::limit($log->system_prompt, 50) }}</td>
                <td>{{ Str::limit($log->violations ?? '-', 50) }}</td>
                <td>{{ $log->created_at }}</td>
                <td>
                    <a href="{{ route('admin.wmcp.governance.show', $log->id) }}" class="btn btn-sm btn-outline-primary">Inspect</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $logs->links() }}
</div>
@endsection
