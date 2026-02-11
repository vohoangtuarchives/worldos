@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 fw-bold text-success">WORLD OS // GOD CONSOLE</h1>
            <p class="text-muted small mb-0">Monitoring World ID: {{ $worldId }}</p>
        </div>
        <div class="text-end">
            <div class="small text-muted text-uppercase">Current Stage</div>
            <div class="h4 fw-bold text-primary">{{ strtoupper($powerState->current_stage ?? 'UNKNOWN') }}</div>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- LEFT COLUMN: STATUS & PRESSURE -->
        <div class="col-12 col-md-4">
            
            <!-- Pressure Gauge -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title text-danger mb-0 fw-bold">WORLD PRESSURE</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-danger">{{ number_format($currentPressure * 100, 1) }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ min($currentPressure * 100, 100) }}%" 
                             aria-valuenow="{{ number_format($currentPressure * 100, 1) }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Accumulated strain on reality. >40% triggers Mortal Martial.</p>
                </div>
            </div>

            <!-- Myths (Immutable) -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title text-primary mb-0 fw-bold">MYTHS (IMMUTABLE)</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($myths as $myth)
                            <li class="list-group-item border-start border-4 border-primary">
                                <div class="fw-bold">{{ $myth->truth_statement }}</div>
                                <div class="small text-muted">Rigidity: {{ $myth->rigidity }} | Origin: {{ $myth->origin_event_id }}</div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted fst-italic">No Myths established.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Scars (Permanent) -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title text-warning text-dark mb-0 fw-bold">SCARS (CONSTRAINTS)</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($scars as $scar)
                            <li class="list-group-item border-start border-4 border-warning">
                                <div class="fw-bold">{{ $scar->constraint_rule }}</div>
                                <div class="small text-muted">Scope: {{ $scar->location_scope }} | Severity: {{ $scar->severity }}</div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted fst-italic">World is pristine (No Scars).</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>

        <!-- MIDDLE COLUMN: MEMORY & LOGS -->
        <div class="col-12 col-md-5">
            
            <!-- Contradiction Memory -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title text-warning text-dark mb-0 fw-bold">AI MEMORY (CONTRADICTIONS)</h5>
                </div>
                <div class="card-body">
                    <div class="overflow-auto" style="max-height: 250px;">
                        @forelse($memories as $memory)
                            <div class="p-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-dark">{{ $memory->contradiction_id }}</span>
                                    <span class="small text-muted">{{ $memory->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="small mt-1">
                                    Strategy: <span class="text-info fw-bold">{{ strtoupper($memory->strategy_used) }}</span>
                                    @if($memory->effectiveness)
                                        | Eff: {{ $memory->effectiveness }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-muted fst-italic small">No contradictions resolved yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Event Ledger Stream -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title text-success mb-0 fw-bold">EVENT LEDGER STREAM</h5>
                </div>
                <div class="card-body font-monospace small">
                    <div class="overflow-auto" style="max-height: 400px;">
                        @forelse($recentEvents as $event)
                            <div class="border-bottom pb-2 mb-2">
                                <span class="text-success fw-bold">[{{ $event->epoch }}]</span>
                                <span class="text-primary">{{ $event->event_type }}:</span>
                                <span class="text-dark">{{ $event->description }}</span>
                            </div>
                        @empty
                            <div class="text-muted fst-italic">No events recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: GOD TOOLS -->
        <div class="col-12 col-md-3">
            
            <!-- Inject Event -->
            <div class="card mb-4 shadow border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold">
                        <span class="me-2">⚡</span> MIRACLE
                    </h5>
                    <div class="small opactiy-75">Inject Event</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('writer.world.inject') }}" method="POST">
                        @csrf
                        <input type="hidden" name="world_id" value="{{ $worldId }}">
                        
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Event Type</label>
                            <input type="text" name="event_type" placeholder="e.g. Divine Intervention" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Description</label>
                            <textarea name="description" placeholder="Describe the event..." rows="3" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small text-uppercase text-muted fw-bold">Magnitude</label>
                                <input type="number" step="0.1" name="magnitude" placeholder="0.0-1.0" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-uppercase text-muted fw-bold">Permanence</label>
                                <input type="number" step="0.1" name="permanence" placeholder="0.0-1.0" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Visibility</label>
                            <select name="visibility" class="form-select form-select-sm">
                                <option value="public">Public (Everyone knows)</option>
                                <option value="rumor">Rumor (Vague whispers)</option>
                                <option value="secret">Secret (Hidden truth)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_contradiction" value="1" class="form-check-input" id="checkContradiction">
                            <label class="form-check-label small text-warning fw-bold" for="checkContradiction">
                                ⚠️ Simulate Conflict?
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <span class="me-2">⚡</span> CAST MIRACLE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Create Scar -->
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0 fw-bold">
                        <span class="me-2">💀</span> SMITE
                    </h5>
                    <div class="small opactiy-75">Brand Scar</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('writer.world.scar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="world_id" value="{{ $worldId }}">
                        
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Target Location</label>
                            <input type="text" name="location_scope" placeholder="e.g. Northern Wastes" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Constraint Rule</label>
                            <textarea name="constraint_rule" placeholder="e.g. No magic allowed..." rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Severity</label>
                            <input type="number" step="0.1" name="severity" placeholder="0.0-1.0" class="form-control form-control-sm">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            <span class="me-2">💀</span> BRAND SCAR
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-8 border-b border-gray-700 pb-4">
        <div>
            <h1 class="text-3xl font-bold text-emerald-400">WORLD OS // GOD CONSOLE</h1>
            <p class="text-sm text-gray-400">Monitoring World ID: {{ $worldId }}</p>
        </div>
        <div class="text-right">
            <div class="text-xs text-gray-500">CURRENT STAGE</div>
            <div class="text-2xl font-bold text-purple-400">{{ strtoupper($powerState->current_stage ?? 'UNKNOWN') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        
        <!-- LEFT COLUMN: STATUS & PRESSURE -->
        <div class="col-span-12 md:col-span-4 space-y-6">
            
            <!-- Pressure Gauge -->
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="text-lg font-bold text-red-400 mb-2">WORLD PRESSURE</h3>
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-600 bg-red-200">
                                {{ number_format($currentPressure * 100, 1) }}%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-4 mb-4 text-xs flex rounded bg-red-200">
                        <div style="width:{{ min($currentPressure * 100, 100) }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-red-500 transition-all duration-500"></div>
                    </div>
                    <p class="text-xs text-gray-400">Accumulated strain on reality. >40% triggers Mortal Martial.</p>
                </div>
            </div>

            <!-- Myths (Immutable) -->
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="text-lg font-bold text-blue-400 mb-2">MYTHS (IMMUTABLE)</h3>
                <ul class="space-y-2">
                    @forelse($myths as $myth)
                        <li class="bg-gray-900 p-2 rounded border-l-4 border-blue-500">
                            <div class="text-sm font-bold">{{ $myth->truth_statement }}</div>
                            <div class="text-xs text-gray-500">Rigidity: {{ $myth->rigidity }} | Origin: {{ $myth->origin_event_id }}</div>
                        </li>
                    @empty
                        <li class="text-gray-600 italic">No Myths established.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Scars (Permanent) -->
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="text-lg font-bold text-orange-400 mb-2">SCARS (CONSTRAINTS)</h3>
                <ul class="space-y-2">
                    @forelse($scars as $scar)
                        <li class="bg-gray-900 p-2 rounded border-l-4 border-orange-500">
                            <div class="text-sm font-bold">{{ $scar->constraint_rule }}</div>
                            <div class="text-xs text-orange-300">Scope: {{ $scar->location_scope }} | Severity: {{ $scar->severity }}</div>
                        </li>
                    @empty
                        <li class="text-gray-600 italic">World is pristine (No Scars).</li>
                    @endforelse
                </ul>
            </div>

        </div>

        <!-- MIDDLE COLUMN: MEMORY & LOGS -->
        <div class="col-span-12 md:col-span-5 space-y-6">
            
            <!-- Contradiction Memory -->
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="text-lg font-bold text-yellow-400 mb-2">AI MEMORY (CONTRADICTIONS)</h3>
                <div class="overflow-y-auto max-h-64 space-y-2">
                    @forelse($memories as $memory)
                        <div class="bg-gray-900 p-2 rounded text-xs">
                            <div class="flex justify-between">
                                <span class="font-bold text-yellow-200">{{ $memory->contradiction_id }}</span>
                                <span class="text-gray-500">{{ $memory->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-1">
                                Strategy: <span class="text-cyan-400">{{ strtoupper($memory->strategy_used) }}</span>
                                @if($memory->effectiveness)
                                    | Eff: {{ $memory->effectiveness }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-600 italic text-sm">No contradictions resolved yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Event Ledger Stream -->
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="text-lg font-bold text-green-400 mb-2">EVENT LEDGER STREAM</h3>
                <div class="overflow-y-auto max-h-96 space-y-2 font-mono text-xs">
                    @forelse($recentEvents as $event)
                        <div class="border-b border-gray-700 pb-2">
                            <span class="text-green-500">[{{ $event->epoch }}]</span>
                            <span class="text-blue-300">{{ $event->event_type }}:</span>
                            <span class="text-gray-300">{{ $event->description }}</span>
                        </div>
                    @empty
                        <div class="text-gray-600 italic">No events recorded.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: GOD TOOLS -->
        <div class="col-span-12 md:col-span-3 space-y-6">
            
            <!-- Inject Event -->
            <div class="bg-gray-800 rounded border border-pink-900 overflow-hidden shadow-lg shadow-pink-900/20">
                <div class="bg-pink-900/50 p-3 border-b border-pink-800">
                    <h3 class="text-sm font-bold text-pink-300 flex items-center">
                        <span class="mr-2">⚡</span> MIRACLE (INJECT EVENT)
                    </h3>
                </div>
                <div class="p-4">
                    <form action="{{ route('writer.world.inject') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="world_id" value="{{ $worldId }}">
                        
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Event Type</label>
                            <input type="text" name="event_type" placeholder="e.g. Divine Intervention" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-pink-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Description</label>
                            <textarea name="description" placeholder="Describe the event..." rows="3" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-pink-500 focus:outline-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] uppercase text-gray-400 mb-1">Magnitude</label>
                                <input type="number" step="0.1" name="magnitude" placeholder="0.0 - 1.0" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-pink-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase text-gray-400 mb-1">Permanence</label>
                                <input type="number" step="0.1" name="permanence" placeholder="0.0 - 1.0" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-pink-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Visibility</label>
                            <select name="visibility" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-pink-500 focus:outline-none">
                                <option value="public">Public (Everyone knows)</option>
                                <option value="rumor">Rumor (Vague whispers)</option>
                                <option value="secret">Secret (Hidden truth)</option>
                            </select>
                        </div>
                        
                        <div class="pt-2 border-t border-gray-700">
                            <label class="flex items-center space-x-2 text-xs text-yellow-400 cursor-pointer hover:text-yellow-300">
                                <input type="checkbox" name="is_contradiction" value="1" class="rounded bg-gray-900 border-gray-600 text-pink-600 focus:ring-pink-500">
                                <span>⚠️ Simulate Gate Conflict?</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-pink-600 hover:bg-pink-500 text-white font-bold py-3 px-4 rounded shadow-md border border-pink-400 flex justify-center items-center transition-all">
                            <span class="mr-2">⚡</span> CAST MIRACLE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Create Scar -->
            <div class="bg-gray-800 rounded border border-red-900 overflow-hidden shadow-lg shadow-red-900/20">
                <div class="bg-red-900/50 p-3 border-b border-red-800">
                    <h3 class="text-sm font-bold text-red-300 flex items-center">
                        <span class="mr-2">💀</span> SMITE (BRAND SCAR)
                    </h3>
                </div>
                <div class="p-4">
                    <form action="{{ route('writer.world.scar') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="world_id" value="{{ $worldId }}">
                        
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Target Location</label>
                            <input type="text" name="location_scope" placeholder="e.g. The Northern Wastes" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Constraint Rule</label>
                            <textarea name="constraint_rule" placeholder="e.g. No magic allowed here..." rows="2" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-red-500 focus:outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase text-gray-400 mb-1">Severity</label>
                            <input type="number" step="0.1" name="severity" placeholder="0.0 - 1.0" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-xs text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-3 px-4 rounded shadow-md border border-red-500 flex justify-center items-center transition-all">
                            <span class="mr-2">💀</span> BRAND SCAR
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg border border-green-400 animate-bounce">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded shadow-lg border border-red-400 animate-pulse">
            {{ session('error') }}
        </div>
    @endif

</div>
@endsection
