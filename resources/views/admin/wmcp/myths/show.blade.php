@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{{ $myth->name }}</h1>
    <a href="{{ route('admin.wmcp.myths.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">🧠 Myth Details</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">World:</th>
                        <td>
                            <a href="{{ route('admin.wmcp.worlds.show', $myth->world_id) }}">
                                {{ $myth->world->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td><strong>{{ $myth->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $myth->status === 'active' ? 'success' : ($myth->status === 'decaying' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($myth->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Strength:</th>
                        <td><span class="badge bg-primary fs-5">{{ $myth->strength }}</span></td>
                    </tr>
                    <tr>
                        <th>Emerged:</th>
                        <td>{{ $myth->created_at->format('Y-m-d H:i') }} ({{ $myth->created_at->diffForHumans() }})</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($myth->beliefs->isNotEmpty())
            <div class="card">
                <div class="card-header">🔗 Supporting Beliefs ({{ $myth->beliefs->count() }})</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($myth->beliefs as $belief)
                            <li class="list-group-item">
                                <strong>{{ $belief->content }}</strong><br>
                                <small class="text-muted">Strength: {{ $belief->strength }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="alert alert-warning">
            <strong>🔒 Semi-Mutable</strong><br>
            This myth emerged organically and can decay or merge, but cannot be manually deleted.
        </div>

        @if($myth->status === 'merged')
            <div class="alert alert-secondary">
                <strong>🔗 Merged</strong><br>
                This myth has merged with another myth and no longer has individual strength.
            </div>
        @endif
    </div>
</div>
@endsection
