@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1>Create Incident Report</h1>
    <h5 class="text-muted">Target World: {{ $world->name }} (ID: {{ $world->id }})</h5>
    
    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('admin.wmcp.incidents.store', $world) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Severity Level</label>
                    <select name="severity" class="form-select" required>
                        <option value="CRITICAL">🔴 CRITICAL (Service Outage, Fatal Logic)</option>
                        <option value="HIGH">🟠 HIGH (Major Degradation)</option>
                        <option value="MEDIUM">🟡 MEDIUM (Minor Logic Issue)</option>
                        <option value="LOW">🔵 LOW (Non-Urgent)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Incident Summary</label>
                    <input type="text" name="summary" class="form-control" placeholder="Short description of the event" required>
                    <div class="form-text">Example: "Economy collapse due to infinite gold loop" or "Logic fork explosion".</div>
                </div>

                <div class="alert alert-info">
                    <strong>Note:</strong> Creating this report sets the status to <strong>DETECTED</strong>. You will fill in the detailed analysis (Timeline, RCA, 5 Whys) in the next step.
                </div>

                <button type="submit" class="btn btn-primary">Initialize Investigation</button>
            </form>
        </div>
    </div>
</div>
@endsection
