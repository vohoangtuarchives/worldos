@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">📝 Propose New Primitive</h1>
    <a href="{{ route('admin.wmcp.primitives.index') }}" class="btn btn-sm btn-secondary">← Back to Catalog</a>
</div>

<div class="alert alert-warning mb-4">
    <strong>⚠️ Governance Process:</strong> Primitive proposals require ADR approval and will NOT be auto-merged. 
    Each proposal must justify:
    <ul class="mb-0 mt-2">
        <li>Why existing primitives are insufficient</li>
        <li>How this affects world power balance</li>
        <li>What this enables/forbids</li>
    </ul>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.wmcp.primitives.submit_proposal') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Proposed Code <span class="text-danger">*</span></label>
                <input type="text" name="proposed_code" class="form-control" required 
                       placeholder="UPPERCASE_SNAKE_CASE">
                <small class="text-muted">Example: SPIRIT_MONARCHY, JADE_ECONOMY</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Domain <span class="text-danger">*</span></label>
                <select name="domain" class="form-select" required>
                    <option value="">Select Domain</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->value }}">{{ $domain->label() }} - {{ $domain->description() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required 
                       placeholder="Human-readable name">
            </div>

            <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="3" required 
                          placeholder="What this primitive represents"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Justification <span class="text-danger">*</span></label>
                <textarea name="justification" class="form-control" rows="4" required 
                          placeholder="Why existing primitives are insufficient? What story gap does this fill?"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Power Shift Analysis</label>
                <textarea name="power_analysis" class="form-control" rows="3" 
                          placeholder="How does this affect world power balance? What does it enable/forbid?"></textarea>
            </div>

            <div class="alert alert-info">
                <strong>📌 Next Steps:</strong>
                <ol class="mb-0">
                    <li>Proposal logged for review</li>
                    <li>Create ADR document</li>
                    <li>Engineering review</li>
                    <li>Approve & merge to WFR vX.Y.0</li>
                </ol>
            </div>

            <button type="submit" class="btn btn-primary">Submit Proposal</button>
        </form>
    </div>
</div>
@endsection
