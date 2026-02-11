@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🌱 Seed Library</h1>
        <small class="text-muted">Reusable narrative seed templates</small>
    </div>
    <div>
        <a href="{{ route('admin.wmcp.seeds.create') }}" class="btn btn-primary">+ New Seed Template</a>
    </div>
</div>

<div class="alert alert-info mb-3">
    <strong>AFR Compliance:</strong> All Seeds are subject to World Law validation. No bypass permitted.
</div>

<!-- Seeds Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Dimension</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seeds as $seed)
                    <tr>
                        <td><strong>{{ $seed->name }}</strong></td>
                        <td><span class="badge bg-secondary">{{ $seed->type }}</span></td>
                        <td><code class="small">{{ $seed->dimension }}</code></td>
                        <td>
                            <span class="badge bg-{{ $seed->severity >= 7 ? 'danger' : ($seed->severity >= 4 ? 'warning' : 'info') }}">
                                {{ $seed->severity }}
                            </span>
                        </td>
                        <td>
                            @if($seed->is_active)
                                <span class="badge bg-success">✅ Active</span>
                            @else
                                <span class="badge bg-secondary">📦 Archived</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $seed->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.wmcp.seeds.edit', $seed->id) }}" 
                               class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.wmcp.seeds.destroy', $seed->id) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('Delete this seed template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No seed templates yet. Create your first template!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $seeds->links() }}
        </div>
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>💡 Tip:</strong> Seed templates can be injected into Worlds from the World detail page.
</div>
@endsection
