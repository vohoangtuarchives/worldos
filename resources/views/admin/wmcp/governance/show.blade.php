@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Governance Log #{{ $log->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
         <span class="badge bg-{{ $log->status === 'ACCEPTED' ? 'success' : ($log->status === 'FAILED' ? 'dark' : 'danger') }} fs-5">
            {{ $log->status }}
        </span>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4>Request</h4>
        <div class="mb-3">
            <label class="form-label">System Prompt</label>
            <textarea class="form-control" rows="5" readonly>{{ $log->system_prompt }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">User Prompt</label>
            <textarea class="form-control" rows="5" readonly>{{ $log->user_prompt }}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <h4>Response</h4>
        <div class="mb-3">
            <label class="form-label">AI Output</label>
            <textarea class="form-control" rows="10" readonly>{{ $log->response_content }}</textarea>
        </div>
        @if($log->violations)
        <div class="alert alert-danger">
            <strong>Violations:</strong>
            <pre>{{ $log->violations }}</pre>
        </div>
        @endif
    </div>
</div>

<hr>

<h3>Extracted Claims</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Type</th>
            <th>Magnitude</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Reason</th>
        </tr>
    </thead>
    <tbody>
        @foreach($claims as $claim)
        <tr class="{{ $claim->is_valid ? '' : 'table-danger' }}">
            <td>{{ $claim->claim_type }}</td>
            <td>{{ $claim->magnitude }}</td>
            <td>{{ $claim->subject }}</td>
            <td>{{ $claim->is_valid ? 'VALID' : 'INVALID' }}</td>
            <td>{{ $claim->rejection_reason }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
