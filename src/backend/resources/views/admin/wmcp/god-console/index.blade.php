@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">🔮 God Console — {{ $world->name }}</h1>
        <small class="text-muted">Epoch {{ $currentEpoch }} · Cosmic Observatory & Control</small>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.wmcp.worlds.show', $world) }}" class="btn btn-sm btn-outline-secondary">← World</a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- TOP CONTROL BAR                                                    --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="card mb-4 border-dark">
    <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(90deg, #1a1a2e, #16213e);">
        {{-- CHS --}}
        <div class="text-center px-3">
            <div class="text-white-50 small">CHS</div>
            @php
                $chsColor = $metrics['meta']['chs'] >= 0.7 ? '#00ff88' : ($metrics['meta']['chs'] >= 0.4 ? '#ffcc00' : '#ff4444');
            @endphp
            <div class="fw-bold fs-4" style="color: {{ $chsColor }}">{{ number_format($metrics['meta']['chs'], 2) }}</div>
        </div>

        {{-- Critical Four --}}
        @foreach($metrics['critical_four'] as $key => $value)
        <div class="text-center px-3">
            <div class="text-white-50 small">{{ $key }}</div>
            @php
                $kColor = match($key) {
                    'SSI' => $value > 1.0 ? '#ff4444' : ($value > 0.8 ? '#ffcc00' : '#00ff88'),
                    'DI' => $value < 0.3 ? '#ff4444' : ($value < 0.5 ? '#ffcc00' : '#00ff88'),
                    'CF' => $value > 0.06 ? '#ff4444' : ($value > 0.03 ? '#ffcc00' : '#00ff88'),
                    'HBR' => $value > 0.25 ? '#ff4444' : ($value > 0.15 ? '#ffcc00' : '#00ff88'),
                    default => '#00ff88'
                };
            @endphp
            <div class="fw-bold fs-5" style="color: {{ $kColor }}">{{ number_format($value, 3) }}</div>
        </div>
        @endforeach

        {{-- Severity Badge --}}
        <div class="text-center px-3">
            <span class="badge fs-6 bg-{{ match($metrics['severity']) { 'CRITICAL' => 'danger', 'WARNING' => 'warning', default => 'success' } }}">
                {{ $metrics['severity'] }}
            </span>
        </div>

        {{-- Control Buttons --}}
        <div class="btn-group">
            <form method="POST" action="{{ route('admin.wmcp.god-console.freeze', $world->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light" title="Freeze">⏸</button>
            </form>
            <form method="POST" action="{{ route('admin.wmcp.god-console.resume', $world->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light" title="Resume">▶</button>
            </form>
            <form method="POST" action="{{ route('admin.wmcp.god-console.step', $world->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light" title="Step 1 Epoch">⏭</button>
            </form>
            <form method="POST" action="{{ route('admin.wmcp.god-console.rollback', $world->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Rollback" onclick="return confirm('Rollback 1 epoch?')">↩</button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ROW 1: STATE RADAR + STABILITY CHARTS                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    {{-- State Radar Chart --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-bold">📡 State Vector</div>
            <div class="card-body">
                <canvas id="stateRadar" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Stability & Entropy Trend --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header fw-bold">📈 Stability & Entropy Trend</div>
            <div class="card-body">
                <canvas id="stabilityChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ROW 2: KPI GROUPS                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    @php
        $groups = [
            ['title' => '🛡️ Stability', 'data' => $metrics['stability'], 'color' => 'danger'],
            ['title' => '🌱 Evolution', 'data' => $metrics['evolution'], 'color' => 'success'],
            ['title' => '⚔️ Power', 'data' => $metrics['power'], 'color' => 'warning'],
            ['title' => '🧠 Memory', 'data' => $metrics['memory'], 'color' => 'info'],
            ['title' => '✨ Emergence', 'data' => $metrics['emergence'], 'color' => 'primary'],
            ['title' => '⚖️ Governance', 'data' => $metrics['governance'], 'color' => 'secondary'],
        ];
    @endphp

    @foreach($groups as $group)
    <div class="col-md-2 mb-3">
        <div class="card h-100 border-{{ $group['color'] }}">
            <div class="card-header bg-{{ $group['color'] }} text-white small fw-bold py-1">
                {{ $group['title'] }}
            </div>
            <div class="card-body py-2">
                @foreach($group['data'] as $key => $value)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small text-uppercase">{{ $key }}</span>
                    <code class="fw-bold">{{ number_format($value, 3) }}</code>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ROW 3: ALERTS + ATTRACTORS                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    {{-- Active Alerts --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold d-flex justify-content-between">
                <span>🚨 Active Alerts</span>
                <span class="badge bg-{{ empty($alerts) ? 'success' : 'danger' }}">{{ count($alerts) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sev</th>
                                <th>Code</th>
                                <th>Value</th>
                                <th>Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alerts as $alert)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ match($alert['severity']) { 'CRITICAL' => 'danger', 'HIGH' => 'warning', default => 'info' } }}">
                                        {{ $alert['severity'] }}
                                    </span>
                                    @if($alert['escalated'] ?? false)
                                        <span class="badge bg-dark">ESC</span>
                                    @endif
                                </td>
                                <td class="fw-bold small">{{ $alert['code'] }}</td>
                                <td><code>{{ number_format($alert['metric_value'], 4) }}</code></td>
                                <td class="text-muted">{{ $alert['threshold'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">✅ No active alerts</td>
                            </tr>
                            @endforelse

                            @foreach($composites as $comp)
                            <tr class="table-danger">
                                <td><span class="badge bg-dark">META</span></td>
                                <td class="fw-bold small">{{ $comp['code'] }}</td>
                                <td colspan="2" class="small">{{ $comp['description'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Attractor Inspector --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold">🔭 Attractor Inspector</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Attractor</th>
                                <th>Distance</th>
                                <th>Equilibrium</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attractors as $att)
                            <tr class="{{ $att['is_current'] ? 'table-primary' : '' }}">
                                <td class="fw-bold">
                                    {{ $att['code'] }}
                                    @if($att['is_current'])
                                        <span class="badge bg-primary">ACTIVE</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $distColor = $att['distance_to_current'] < 0.2 ? 'success' : ($att['distance_to_current'] < 0.5 ? 'warning' : 'secondary');
                                    @endphp
                                    <span class="badge bg-{{ $distColor }}">{{ number_format($att['distance_to_current'], 3) }}</span>
                                </td>
                                <td class="small text-muted">
                                    E={{ $att['equilibrium']['entropy'] ?? '?' }}
                                    S={{ $att['equilibrium']['stability'] ?? '?' }}
                                </td>
                                <td>
                                    @if(!empty($att['transitions']))
                                        <span class="text-muted small">→ {{ implode(', ', $att['transitions']) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ROW 4: EMERGENCY + CONTROL LOG                                    --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    {{-- Emergency Intervention --}}
    <div class="col-md-4">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white fw-bold">🚨 Emergency Intervention</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('admin.wmcp.god-console.emergency', [$world->id, 'entropy-shock']) }}">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="number" name="magnitude" value="0.15" min="0.05" max="0.3" step="0.05" class="form-control form-control-sm">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Inject entropy shock?')">💥 Entropy Shock</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.wmcp.god-console.emergency', [$world->id, 'reduce-rigidity']) }}">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="number" name="reduction" value="0.1" min="0.05" max="0.2" step="0.05" class="form-control form-control-sm">
                            <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Reduce rigidity?')">🔓 Reduce Rigidity</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.wmcp.god-console.emergency', [$world->id, 'force-collapse']) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('⚠️ FORCE COLLAPSE — Are you sure?')">
                            💀 Force Collapse
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.wmcp.god-console.emergency', [$world->id, 'toggle-emergent']) }}">
                        @csrf
                        <input type="hidden" name="disabled" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-dark w-100">
                            🔒 Disable Emergent Archetypes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Trajectory Sparkline --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header fw-bold">📊 Cosmic Trajectory (Last {{ count($trajectoryData) }} Epochs)</div>
            <div class="card-body">
                <canvas id="trajectoryChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// State Radar
const radarCtx = document.getElementById('stateRadar');
new Chart(radarCtx, {
    type: 'radar',
    data: {
        labels: ['Entropy', 'Energy', 'Stability', 'Strain', 'Causality'],
        datasets: [{
            label: 'Current',
            data: @json($stateVector),
            borderColor: 'rgba(0, 255, 136, 0.8)',
            backgroundColor: 'rgba(0, 255, 136, 0.15)',
            pointBackgroundColor: '#00ff88'
        }]
    },
    options: {
        responsive: true,
        scales: {
            r: {
                min: 0, max: 1,
                ticks: { stepSize: 0.2 },
                grid: { color: 'rgba(255,255,255,0.1)' }
            }
        },
        plugins: { legend: { display: false } }
    }
});

// Stability & Entropy Trend
const stabCtx = document.getElementById('stabilityChart');
new Chart(stabCtx, {
    type: 'line',
    data: {
        labels: @json($trajectoryLabels),
        datasets: [
            {
                label: 'Entropy',
                data: @json($trajectoryEntropy),
                borderColor: '#ff6b6b',
                backgroundColor: 'rgba(255,107,107,0.1)',
                fill: true, tension: 0.3
            },
            {
                label: 'Stability',
                data: @json($trajectoryStability),
                borderColor: '#00ff88',
                backgroundColor: 'rgba(0,255,136,0.1)',
                fill: true, tension: 0.3
            },
            {
                label: 'Strain',
                data: @json($trajectoryStrain),
                borderColor: '#ffd93d',
                backgroundColor: 'rgba(255,217,61,0.05)',
                fill: false, tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { min: 0, max: 1 } },
        plugins: { legend: { position: 'top' } }
    }
});

// Trajectory (Energy + Entropy combo)
const trajCtx = document.getElementById('trajectoryChart');
new Chart(trajCtx, {
    type: 'line',
    data: {
        labels: @json($trajectoryLabels),
        datasets: [
            {
                label: 'Energy',
                data: @json($trajectoryEnergy),
                borderColor: '#4ecdc4',
                tension: 0.3
            },
            {
                label: 'Entropy',
                data: @json($trajectoryEntropy),
                borderColor: '#ff6b6b',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { min: 0, max: 1 } },
        plugins: { legend: { position: 'top' } }
    }
});
</script>
@endpush
