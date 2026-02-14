@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Incident Report: {{ $incident->incident_id }}</h1>
        <span class="badge bg-{{ $incident->status === 'RESOLVED' ? 'success' : 'secondary' }} fs-5">{{ $incident->status }}</span>
    </div>

    <!-- Summary Section -->
    <div class="card mb-4">
        <div class="card-header fw-bold">1. Summary</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>World:</strong> {{ $incident->world->name }}</div>
                <div class="col-md-3"><strong>Severity:</strong> <span class="text-{{ $incident->severity === 'CRITICAL' ? 'danger' : 'warning' }}">{{ $incident->severity }}</span></div>
                <div class="col-md-3"><strong>Created:</strong> {{ $incident->created_at }}</div>
                <div class="col-md-3"><strong>Last Updated:</strong> {{ $incident->updated_at }}</div>
            </div>
            <hr>
            <p class="lead">{{ $incident->summary }}</p>
        </div>
    </div>

    <form action="{{ route('admin.wmcp.incidents.update', $incident) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Lifecycle Management -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">Incident Lifecycle Management</div>
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text">Update Status</span>
                    <select name="status" class="form-select">
                        <option value="DETECTED" {{ $incident->status === 'DETECTED' ? 'selected' : '' }}>DETECTED</option>
                        <option value="CONTAINED" {{ $incident->status === 'CONTAINED' ? 'selected' : '' }}>CONTAINED</option>
                        <option value="STABILIZED" {{ $incident->status === 'STABILIZED' ? 'selected' : '' }}>STABILIZED</option>
                        <option value="ANALYZED" {{ $incident->status === 'ANALYZED' ? 'selected' : '' }}>ANALYZED</option>
                        <option value="RESOLVED" {{ $incident->status === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Update Status</button>
                </div>
            </div>
        </div>

        <!-- 4. Root Cause Analysis (RCA) -->
        <div class="card mb-4">
            <div class="card-header fw-bold">4. Root Cause Analysis (RCA)</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Primary Root Cause Category</label>
                    <select name="root_cause" class="form-select">
                        <option value="">-- Select Root Cause --</option>
                        <option value="AI_BEHAVIOR" {{ $incident->root_cause === 'AI_BEHAVIOR' ? 'selected' : '' }}>AI_BEHAVIOR (Hallucination, Drift)</option>
                        <option value="LAW_GAP" {{ $incident->root_cause === 'LAW_GAP' ? 'selected' : '' }}>LAW_GAP (Missing Constraint)</option>
                        <option value="BALANCE_DRIFT" {{ $incident->root_cause === 'BALANCE_DRIFT' ? 'selected' : '' }}>BALANCE_DRIFT (Feedback Loops)</option>
                        <option value="ECONOMY_FEEDBACK_LOOP" {{ $incident->root_cause === 'ECONOMY_FEEDBACK_LOOP' ? 'selected' : '' }}>ECONOMY_FEEDBACK_LOOP</option>
                        <option value="DECEPTION_OVERFLOW" {{ $incident->root_cause === 'DECEPTION_OVERFLOW' ? 'selected' : '' }}>DECEPTION_OVERFLOW</option>
                        <option value="OPERATOR_DELAY" {{ $incident->root_cause === 'OPERATOR_DELAY' ? 'selected' : '' }}>OPERATOR_DELAY</option>
                        <option value="SYSTEM_BUG" {{ $incident->root_cause === 'SYSTEM_BUG' ? 'selected' : '' }}>SYSTEM_BUG</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Timeline of Events -->
        <div class="card mb-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>3. Timeline of Events (Facts only)</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTimelineRow()">+ Add Event</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="timelineTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Time (T+/-)</th>
                            <th>Event Description</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incident->timeline_events ?? [] as $index => $event)
                        <tr>
                            <td><input type="text" name="timeline_events[{{$index}}][time]" class="form-control form-control-sm" value="{{ $event['time'] ?? '' }}" placeholder="T-5m"></td>
                            <td><input type="text" name="timeline_events[{{$index}}][event]" class="form-control form-control-sm" value="{{ $event['event'] ?? '' }}" placeholder="Event happened..."></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        @empty
                        <tr>
                            <td><input type="text" name="timeline_events[0][time]" class="form-control form-control-sm" placeholder="T-0"></td>
                            <td><input type="text" name="timeline_events[0][event]" class="form-control form-control-sm" placeholder="Incident Detected"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. 5 Whys -->
        <div class="card mb-4">
            <div class="card-header fw-bold">5. Why-Chain (Drill down to Design Decision)</div>
            <div class="card-body">
                @php $whys = $incident->five_whys ?? []; @endphp
                @for($i = 0; $i < 5; $i++)
                <div class="input-group mb-2">
                    <span class="input-group-text" style="width: 100px;">Why #{{ $i + 1 }}</span>
                    <input type="text" name="five_whys[]" class="form-control" value="{{ $whys[$i] ?? '' }}" placeholder="{{ $i === 0 ? 'Why did the incident happen?' : 'Why did that happen?' }}">
                </div>
                @endfor
            </div>
        </div>

        <!-- 6. Resolution -->
        <div class="card mb-4">
            <div class="card-header fw-bold">6. Resolution Justification</div>
            <div class="card-body">
                <textarea name="resolution_justification" class="form-control" rows="3" placeholder="Why did you choose Fork/Rollback/Safe Mode?">{{ $incident->resolution_justification }}</textarea>
            </div>
        </div>

        <!-- 7. Action Items -->
        <div class="card mb-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>7. Action Items (Prevent Reoccurrence)</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addActionRow()">+ Add Item</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="actionTable">
                    <thead class="table-light">
                        <tr>
                            <th>Action</th>
                            <th style="width: 15%">Owner</th>
                            <th style="width: 15%">Type</th>
                            <th style="width: 15%">Deadline</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incident->action_items ?? [] as $index => $item)
                        <tr>
                            <td><input type="text" name="action_items[{{$index}}][action]" class="form-control form-control-sm" value="{{ $item['action'] ?? '' }}"></td>
                            <td><input type="text" name="action_items[{{$index}}][owner]" class="form-control form-control-sm" value="{{ $item['owner'] ?? '' }}"></td>
                            <td>
                                <select name="action_items[{{$index}}][type]" class="form-select form-select-sm">
                                    <option value="Preventive" {{ ($item['type'] ?? '') === 'Preventive' ? 'selected' : '' }}>Preventive</option>
                                    <option value="Mitigation" {{ ($item['type'] ?? '') === 'Mitigation' ? 'selected' : '' }}>Mitigation</option>
                                </select>
                            </td>
                            <td><input type="text" name="action_items[{{$index}}][deadline]" class="form-control form-control-sm" value="{{ $item['deadline'] ?? '' }}" placeholder="+7d"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        @empty
                        <!-- Empty row template if needed, or JS handles it -->
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-grid gap-2 mb-5">
            <button type="submit" class="btn btn-success btn-lg">Save Post-Mortem Updates</button>
        </div>
    </form>
</div>

<script>
let timelineIndex = {{ count($incident->timeline_events ?? []) > 0 ? count($incident->timeline_events ?? []) : 1 }};
function addTimelineRow() {
    const tbody = document.querySelector('#timelineTable tbody');
    const row = `
        <tr>
            <td><input type="text" name="timeline_events[${timelineIndex}][time]" class="form-control form-control-sm" placeholder="T+X"></td>
            <td><input type="text" name="timeline_events[${timelineIndex}][event]" class="form-control form-control-sm" placeholder="Event description..."></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
        </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', row);
    timelineIndex++;
}

let actionIndex = {{ count($incident->action_items ?? []) }};
function addActionRow() {
    const tbody = document.querySelector('#actionTable tbody');
    const row = `
        <tr>
            <td><input type="text" name="action_items[${actionIndex}][action]" class="form-control form-control-sm" placeholder="Fix logic..."></td>
            <td><input type="text" name="action_items[${actionIndex}][owner]" class="form-control form-control-sm" placeholder="System/Operator"></td>
            <td>
                <select name="action_items[${actionIndex}][type]" class="form-select form-select-sm">
                    <option value="Preventive">Preventive</option>
                    <option value="Mitigation">Mitigation</option>
                </select>
            </td>
            <td><input type="text" name="action_items[${actionIndex}][deadline]" class="form-control form-control-sm" placeholder="+7d"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
        </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', row);
    actionIndex++;
}
</script>
@endsection
