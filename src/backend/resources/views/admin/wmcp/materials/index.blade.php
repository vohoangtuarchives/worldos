@extends('layouts.admin')

@section('title', 'Materials')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Material Management</h2>
        <a href="{{ route('admin.materials.create') }}" class="btn btn-primary">+ Create Material</a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.materials.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by code or description" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="ontology" class="form-select">
                            <option value="">All Ontologies</option>
                            <option value="symbolic" {{ request('ontology') === 'symbolic' ? 'selected' : '' }}>Symbolic</option>
                            <option value="institutional" {{ request('ontology') === 'institutional' ? 'selected' : '' }}>Institutional</option>
                            <option value="behavioral" {{ request('ontology') === 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="function" class="form-select">
                            <option value="">All Functions</option>
                            <option value="legitimizing" {{ request('function') === 'legitimizing' ? 'selected' : '' }}>Legitimizing</option>
                            <option value="stabilizing" {{ request('function') === 'stabilizing' ? 'selected' : '' }}>Stabilizing</option>
                            <option value="transformative" {{ request('function') === 'transformative' ? 'selected' : '' }}>Transformative</option>
                            <option value="destructive" {{ request('function') === 'destructive' ? 'selected' : '' }}>Destructive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Materials Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Ontology</th>
                        <th>Function</th>
                        <th>Lifecycle</th>
                        <th>Instances</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td><code>{{ $material->code }}</code></td>
                            <td><span class="badge bg-info">{{ $material->ontology->value }}</span></td>
                            <td><span class="badge bg-secondary">{{ $material->function->value }}</span></td>
                            <td><span class="badge bg-success">{{ $material->default_lifecycle->value }}</span></td>
                            <td>{{ $material->instances()->count() }}</td>
                            <td>
                                <a href="{{ route('admin.materials.show', $material->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('admin.materials.edit', $material->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form method="POST" action="{{ route('admin.materials.destroy', $material->id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this material?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No materials found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $materials->links() }}
        </div>
    </div>
</div>
@endsection
