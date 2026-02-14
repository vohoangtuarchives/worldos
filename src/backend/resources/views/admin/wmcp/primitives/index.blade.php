@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🧱 World Foundation Repository</h1>
        <small class="text-muted">Immutable primitive catalog ({{ $version }})</small>
    </div>
    <a href="{{ route('admin.wmcp.primitives.propose') }}" class="btn btn-outline-primary">📝 Propose New Primitive</a>
</div>

<div class="alert alert-info mb-4">
    <strong>ADR-0008:</strong> Primitives are <u>immutable</u>. AI can only reference, never create. Proposals require ADR approval.
</div>

<!-- Version & Domain Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Domain</label>
                <select name="domain" class="form-select" onchange="this.form.submit()">
                    <option value="">All Domains</option>
                    @foreach($domains as $d)
                        <option value="{{ $d->value }}" {{ $domain === $d->value ? 'selected' : '' }}>
                            {{ $d->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">WFR Version</label>
                <select name="version" class="form-select" onchange="this.form.submit()">
                    @foreach($versions as $v)
                        <option value="{{ $v->version }}" {{ $version === $v->version ? 'selected' : '' }}>
                            v{{ $v->version }} {{ $v->is_stable ? '✅' : '🚧' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Primitives Table -->
<div class="card">
    <div class="card-body">
        @if($primitives->isEmpty())
            <p class="text-muted text-center">No primitives found for this filter.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Constraints</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($primitives as $primitive)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $primitive->domain->label() }}</span>
                                </td>
                                <td><code>{{ $primitive->code }}</code></td>
                                <td>{{ $primitive->name }}</td>
                                <td class="small text-muted">{{ Str::limit($primitive->description, 60) }}</td>
                                <td class="small">
                                    @if($primitive->constraints)
                                        @if(isset($primitive->constraints['requires']))
                                            <span class="text-primary">Requires: {{ implode(', ', $primitive->constraints['requires']) }}</span><br>
                                        @endif
                                        @if(isset($primitive->constraints['enables']))
                                            <span class="text-success">Enables: {{ implode(', ', $primitive->constraints['enables']) }}</span><br>
                                        @endif
                                        @if(isset($primitive->constraints['forbids']))
                                            <span class="text-danger">Forbids: {{ implode(', ', $primitive->constraints['forbids']) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.wmcp.primitives.show', $primitive->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="alert alert-secondary mt-3">
    <strong>🔒 Read-Only:</strong> Primitives cannot be edited or deleted. Only versioned additions allowed.
</div>
@endsection
