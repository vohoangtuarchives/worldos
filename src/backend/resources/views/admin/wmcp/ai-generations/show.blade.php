@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">AI Generation #{{ $generation->id }}</h1>
        <small class="text-muted">
            Status: 
            @if($generation->status === 'ACCEPTED')
                <span class="badge bg-success">✅ Accepted</span>
            @elseif($generation->status === 'REJECTED')
                <span class="badge bg-danger">❌ Rejected</span>
            @else
                <span class="badge bg-warning">⚠️ {{ $generation->status }}</span>
            @endif
        </small>
    </div>
    <div>
        <a href="{{ route('admin.wmcp.ai-generations.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Metadata -->
        <div class="card mb-3">
            <div class="card-header">Metadata</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>World ID:</strong> {{ $generation->world_id }}
                </li>
                <li class="list-group-item">
                    <strong>Context Type:</strong><br>
                    <code class="small">{{ $generation->context_type ?? 'N/A' }}</code>
                </li>
                <li class="list-group-item">
                    <strong>Attempt Number:</strong> {{ $generation->attempt_number }}
                </li>
                <li class="list-group-item">
                    <strong>Prompt Hash:</strong><br>
                    <code class="small">{{ Str::limit($generation->prompt_hash ?? 'N/A', 30) }}</code>
                </li>
                <li class="list-group-item">
                    <strong>Created:</strong><br>
                    <small>{{ $generation->created_at }}</small>
                </li>
            </ul>
        </div>

        <!-- Extracted Claims -->
        <div class="card border-{{ $claims->count() > 0 ? 'primary' : 'secondary' }}">
            <div class="card-header bg-{{ $claims->count() > 0 ? 'primary' : 'secondary' }} bg-opacity-10">
                <strong>🔍 Extracted Claims ({{ $claims->count() }})</strong>
            </div>
            <div class="card-body">
                @if($claims->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($claims as $claim)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong>{{ $claim->claim_type }}</strong>
                                @if($claim->is_valid)
                                    <span class="badge bg-success">✅ Valid</span>
                                @else
                                    <span class="badge bg-danger">❌ Invalid</span>
                                @endif
                            </div>
                            @if($claim->subject)
                                <div class="small text-muted">Subject: {{ $claim->subject }}</div>
                            @endif
                            @if($claim->magnitude)
                                <div class="small text-muted">Magnitude: {{ $claim->magnitude }}</div>
                            @endif
                            @if(!$claim->is_valid && $claim->rejection_reason)
                                <div class="small text-danger mt-1">
                                    <strong>Reason:</strong> {{ $claim->rejection_reason }}
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small">No claims extracted</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Prompts -->
        <div class="card mb-3">
            <div class="card-header">System Prompt</div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">{{ $generation->system_prompt }}</pre>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">User Prompt</div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">{{ $generation->user_prompt }}</pre>
            </div>
        </div>

        <!-- Response -->
        <div class="card {{ $generation->status === 'REJECTED' ? 'border-danger' : '' }}">
            <div class="card-header bg-{{ $generation->status === 'REJECTED' ? 'danger' : 'success' }} bg-opacity-10">
                AI Response
            </div>
            <div class="card-body">
                @if($generation->response_content)
                    <pre class="bg-light p-3 rounded small" style="max-height: 400px; overflow-y: auto;">{{ $generation->response_content }}</pre>
                @else
                    <p class="text-muted">No response content</p>
                @endif
            </div>
        </div>

        <!-- Violations (if rejected) -->
        @if($generation->status === 'REJECTED' && $generation->violations)
        <div class="alert alert-danger mt-3">
            <strong>❌ Violations:</strong>
            <pre class="mb-0 mt-2 small">{{ json_encode(json_decode($generation->violations), JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
</div>

<div class="alert alert-info mt-3">
    <strong>Article II Compliance:</strong> This generation has been audited. All claims have been extracted and validated per Constitution requirements.
</div>
@endsection
