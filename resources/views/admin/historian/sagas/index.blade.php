@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">📜 Saga Explorer</h1>
</div>

<div class="card">
    <div class="card-header">All Sagas</div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Worlds</th>
                    <th scope="col">Summary</th>
                    <th scope="col">Created</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sagas as $saga)
                <tr>
                    <td>{{ substr($saga->id, 0, 8) }}</td>
                    <td><a href="{{ route('admin.historian.sagas.show', $saga) }}">{{ $saga->name }}</a></td>
                    <td>
                        <span class="badge bg-{{ $saga->status === 'completed' ? 'success' : ($saga->status === 'failed' ? 'danger' : 'warning') }}">
                            {{ $saga->status }}
                        </span>
                    </td>
                    <td>{{ $saga->world_count }}</td>
                    <td class="text-truncate" style="max-width: 250px;">{{ $saga->summary }}</td>
                    <td>{{ $saga->created_at->format('M d, H:i') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.historian.sagas.show', $saga) }}" class="btn btn-outline-primary">Analyze</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $sagas->links() }}
    </div>
</div>
@endsection
