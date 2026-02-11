@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{{ $primitive->domain->label() }} - {{ $primitive->name }}</h1>
    <a href="{{ route('admin.wmcp.primitives.index') }}" class="btn btn-sm btn-secondary">← Back to Catalog</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">📋 Primitive Details (Read-Only)</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Code:</th>
                        <td><code class="fs-5">{{ $primitive->code }}</code></td>
                    </tr>
                    <tr>
                        <th>Domain:</th>
                        <td><span class="badge bg-secondary">{{ $primitive->domain->label() }}</span></td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $primitive->name }}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $primitive->description }}</td>
                    </tr>
                    <tr>
                        <th>Introduced in:</th>
                        <td><span class="badge bg-info">WFR v{{ $primitive->version }}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        @if($primitive->constraints)
            <div class="card mb-3">
                <div class="card-header">⚙️ Constraints</div>
                <div class="card-body">
                    @if(isset($primitive->constraints['requires']))
                        <div class="mb-2">
                            <strong class="text-primary">Requires:</strong>
                            <ul>
                                @foreach($primitive->constraints['requires'] as $req)
                                    <li>{{ $req }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($primitive->constraints['enables']))
                        <div class="mb-2">
                            <strong class="text-success">Enables:</strong>
                            <ul>
                                @foreach($primitive->constraints['enables'] as $enable)
                                    <li>{{ $enable }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($primitive->constraints['forbids']))
                        <div class="mb-2">
                            <strong class="text-danger">Forbids:</strong>
                            <ul>
                                @foreach($primitive->constraints['forbids'] as $forbid)
                                    <li>{{ $forbid }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="alert alert-warning">
            <strong>🔒 Immutable</strong><br>
            This primitive cannot be edited or deleted.
        </div>

        <div class="alert alert-info">
            <strong>📚 Usage:</strong><br>
            AI and Seeds can reference this primitive by code: <code>{{ $primitive->code }}</code>
        </div>
    </div>
</div>
@endsection
