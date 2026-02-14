@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🤖 AI Generations</h1>
        <small class="text-muted">Article II Compliance: All AI outputs must have audit trail</small>
    </div>
</div>

<!-- Constitution Reference -->
<div class="alert alert-info mb-3">
    <strong>⚖️ Constitution Article II:</strong> "Mọi output của AI phải: Có claim, Có validation, Có audit"
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.wmcp.ai-generations.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">World</label>
                    <select name="world_id" class="form-select form-select-sm">
                        <option value="">All Worlds</option>
                        @foreach($worlds as $w)
                            <option value="{{ $w->id }}" {{ request('world_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Context Type</label>
                    <select name="context_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($contextTypes as $type)
                            <option value="{{ $type }}" {{ request('context_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>✅ Accepted</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>❌ Rejected</option>
                        <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>⚠️ Failed</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Generations Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>World</th>
                        <th>Context</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($generations as $gen)
                    <tr>
                        <td class="text-muted small">#{{ $gen->id }}</td>
                        <td><span class="badge bg-secondary">World {{ $gen->world_id }}</span></td>
                        <td><code class="small">{{ $gen->context_type ?? 'N/A' }}</code></td>
                        <td>
                            @if($gen->status === 'ACCEPTED')
                                <span class="badge bg-success">✅ Accepted</span>
                            @elseif($gen->status === 'REJECTED')
                                <span class="badge bg-danger">❌ Rejected</span>
                            @else
                                <span class="badge bg-warning">⚠️ {{ $gen->status }}</span>
                            @endif
                        </td>
                        <td><span class="badge bg-info">{{ $gen->attempt_number }}</span></td>
                        <td class="text-muted small">{{ \Carbon\Carbon::parse($gen->created_at)->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.wmcp.ai-generations.show', $gen->id) }}" 
                               class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No AI generations found. Try adjusting filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $generations->links() }}
        </div>
    </div>
</div>

<div class="alert alert-warning mt-3">
    <strong>🔒 Read-Only:</strong> This interface is for audit purposes only. AI outputs cannot be manually modified per AFR v1.0.
</div>
@endsection
