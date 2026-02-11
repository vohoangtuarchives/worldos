@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $world->name }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.wmcp.worlds.factors', $world->id) }}" class="btn btn-primary me-2">📊 World Factors</a>
            <a href="{{ route('admin.wmcp.worlds.edit', $world->id) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>
    <div class="mb-3">
        <small class="text-muted">World ID: {{ $world->id }} | Tick: {{ $world->clock->current_tick ?? 0 }}</small>
        <span class="badge bg-{{ $world->status === 'ACTIVE' ? 'success' : ($world->status === 'SAFE_MODE' ? 'warning' : 'secondary') }} fs-6 ms-3">
            {{ $world->status }}
        </span>
    </div>

<!-- Metadata Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>World Metadata</strong>
                <a href="{{ route('admin.wmcp.worlds.edit', $world->id) }}" class="btn btn-sm btn-outline-primary">
                    ✏️ Edit Metadata
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="fw-bold">Type</label>
                        <div>{{ $world->type?->label() ?? '—' }}</div>
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="fw-bold">Tags</label>
                        <div>
                            @if($world->tags && count($world->tags) > 0)
                                @foreach($world->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No tags</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold">Description</label>
                        <div class="text-muted">{{ $world->description ?? 'No description provided.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Governance & Law Column -->
    <div class="col-md-4">
        <!-- Constitution Reference -->
        <div class="alert alert-info mb-3">
            <strong>⚖️ Constitution Article I</strong><br>
            <small>"World Law Profile là luật tối cao của mỗi world. Không có entity nào (AI hay human) được vượt World Law."</small>
        </div>

        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                <span><strong>🔒 World Law Profile</strong></span>
                <a href="{{ route('admin.wmcp.worlds.edit_laws', $world->id) }}" class="btn btn-sm btn-warning" 
                   title="Constitutional Amendment - Use with extreme caution">
                    ⚠️ Edit Laws
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 px-2 small mb-3">
                    <strong>Warning:</strong> Editing World Laws is equivalent to a constitutional amendment. All existing content must comply.
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Magic System</label>
                    <div class="text-muted">{{ $world->law_profile->magicSystem->value ?? 'None' }}</div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Technology Level</label>
                    <div class="text-muted">{{ $world->law_profile->techLevel->value ?? 'None' }}</div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Power Ceiling</label>
                    <div class="text-muted">{{ $world->law_profile->powerCeiling->value ?? 'None' }}</div>
                </div>
                <hr>
                <details>
                    <summary>Full JSON Profile</summary>
                    <pre class="mt-2 text-wrap bg-light p-2 rounded"><code>{{ json_encode($world->law_profile, JSON_PRETTY_PRINT) }}</code></pre>
                </details>
            </div>
        </div>

        <!-- Operator Controls -->
            <div class="card mb-3 border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <strong>🚨 Emergency Controls</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.wmcp.worlds.safe_mode', $world->id) }}" method="POST">
                        @csrf
                        @if($world->status === 'SAFE_MODE')
                            <button type="submit" class="btn btn-success w-100 mb-2">✅ Deactivate Safe Mode</button>
                        @else
                            <button type="submit" class="btn btn-warning w-100 mb-2">⚠️ Activate Safe Mode</button>
                        @endif
                    </form>

                    <form action="{{ route('admin.wmcp.worlds.halt', $world->id) }}" method="POST" 
                          onsubmit="return confirm('⚠️ EMERGENCY HALT: This will stop the world immediately. Continue?')">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">⛔ KILL SWITCH</button>
                    </form>
                </div>
            </div>

        <!-- Danger Zone -->
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <strong>⚠️ DANGER ZONE</strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <strong>Article III:</strong> "Kill World là hành động không thể đảo ngược và phải được xem như kết thúc một thực thể sống."
                </p>
                
                <button type="button" class="btn btn-outline-secondary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#forkModal">
                    🔀 Fork Timeline
                </button>
                <small class="text-muted d-block mb-3">Article V: Fork requires justification</small>

                @if($world->status !== 'LOCKED')
                <form action="{{ route('admin.wmcp.worlds.halt', $world->id) }}" method="POST" 
                      onsubmit="return confirm('⛔ EMERGENCY STOP\n\nThis action will HALT the world and create a CRITICAL alert.\n\nArticle III: This is an irreversible action that ends a living entity.\n\nAre you absolutely sure?')">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">
                        ⛔ KILL SWITCH
                    </button>
                </form>
                <small class="text-muted d-block mt-1">Only use in existential failures</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Timeline/Events Column -->
    <div class="col-md-8">
        <h3>Recent Events</h3>
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Tick</th>
                        <th>Type</th>
                        <th>Summary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($world->events as $event)
                    <tr>
                        <td>{{ $event->tick }}</td>
                        <td>{{ $event->type }}</td>
                        <td>{{ Str::limit($event->payload['summary'] ?? '-', 80) }}</td>
                        <td>
                            <button class="btn btn-xs btn-outline-primary" title="Fork from here">Fork</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No events recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Factions</h5>
                <a href="{{ route('admin.wmcp.factions.create', $world) }}" class="btn btn-sm btn-primary">Add Faction</a>
            </div>
            <div class="card-body">
                @if($world->factions->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Cohesion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($world->factions as $faction)
                                <tr>
                                    <td>{{ $faction->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $faction->type }}</span></td>
                                    <td>
                                        @php $cohesion = $faction->attributes['cohesion'] ?? 0; @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $cohesion < 50 ? 'danger' : 'success' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $cohesion }}%">
                                                {{ $cohesion }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.wmcp.factions.edit', $faction) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form action="{{ route('admin.wmcp.factions.destroy', $faction) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete faction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">No factions registered.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Fork Modal -->
<div class="modal fade" id="forkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('admin.wmcp.worlds.fork', $world->id) }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header bg-warning bg-opacity-10">
            <h5 class="modal-title">🔀 Fork Timeline</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning small">
                <strong>Article V:</strong> Fork là bảo tồn, không phải trốn tránh. Fork chỉ hợp lệ khi có lý do rõ ràng.
            </div>
            
            <div class="mb-3">
                <label class="form-label">New Timeline Name</label>
                <input type="text" name="new_name" class="form-control" value="Fork of {{ $world->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Fork at Tick</label>
                <input type="number" name="tick" class="form-control" value="{{ $world->clock->current_tick ?? 0 }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Justification (Required)</strong></label>
                <textarea name="justification" class="form-control" rows="3" 
                          placeholder="Why are you forking? (e.g., Testing alternative law profile, Recovering from corruption, etc.)" 
                          required></textarea>
                <small class="text-muted">This will be logged in the audit trail.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">Create Fork</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection
