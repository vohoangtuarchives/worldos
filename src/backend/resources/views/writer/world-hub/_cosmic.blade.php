{{-- COSMIC OBSERVATORY TAB --}}
<div class="space-y-6">
    {{-- META LAYER DASHBOARD --}}
    @if(isset($metaLayer))
    <div class="bg-gradient-to-r from-indigo-900 to-purple-900 rounded-lg border border-indigo-500/50 p-6 shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
        </div>
        
        <div class="flex justify-between items-start mb-6 relative z-10">
            <div>
                <h3 class="text-xl font-bold text-white flex items-center">
                    <span class="mr-2">🌌</span> Meta-Layer Consciousness
                </h3>
                <div class="text-indigo-200 text-sm mt-1">Era {{ $metaLayer['era'] }} • Flux: {{ number_format($metaLayer['flux'], 3) }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-indigo-300 uppercase tracking-wider">System Stability</div>
                <div class="text-2xl font-bold {{ $metaLayer['stability'] > 0.5 ? 'text-green-400' : 'text-red-400' }}">
                    {{ number_format($metaLayer['stability'] * 100, 1) }}%
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
            {{-- Chaos Pool --}}
            <div class="bg-black/30 rounded-lg p-4 backdrop-blur-sm border border-white/10">
                <div class="text-xs text-gray-400 mb-1">Chaos Entropy Pool</div>
                <div class="text-3xl font-mono text-purple-400">{{ number_format($metaLayer['chaos'], 2) }}</div>
                <div class="w-full bg-gray-700/50 rounded-full h-1.5 mt-3">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ min(100, ($metaLayer['chaos'] / 150) * 100) }}%"></div>
                </div>
                <div class="text-[10px] text-gray-500 mt-1 flex justify-between">
                    <span>Safe</span>
                    <span>Extinction Event (>150)</span>
                </div>
            </div>

            {{-- Ideology Vector --}}
            <div class="md:col-span-2 bg-black/30 rounded-lg p-4 backdrop-blur-sm border border-white/10">
                <div class="text-xs text-gray-400 mb-2">Ideological Spectrum</div>
                <div class="grid grid-cols-5 gap-2 h-full items-end">
                    @foreach($metaLayer['ideology'] as $key => $val)
                    <div class="flex flex-col items-center group">
                        <div class="w-full bg-gray-700/50 rounded-t-sm relative h-16 w-8 overflow-hidden hover:bg-gray-600/50 transition-colors">
                            <div class="absolute bottom-0 w-full bg-gradient-to-t from-blue-500 to-cyan-400 transition-all duration-500" 
                                 style="height: {{ $val * 100 }}%"></div>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-2 uppercase tracking-tighter">{{ substr($key, 0, 4) }}</span>
                        <div class="absolute -mt-8 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            {{ $key }}: {{ number_format($val, 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Bar: CHS + Critical Four + Severity --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-3 flex items-center justify-between flex-wrap gap-4" style="background: linear-gradient(90deg, #1a1a2e, #16213e); border-radius: 0.5rem;">
            {{-- CHS --}}
            <div class="text-center px-3">
                <div class="text-gray-400 text-xs">CHS</div>
                @php
                    $chsColor = ($metrics['meta']['chs'] ?? 0) >= 0.7 ? '#00ff88' : (($metrics['meta']['chs'] ?? 0) >= 0.4 ? '#ffcc00' : '#ff4444');
                @endphp
                <div class="font-bold text-2xl" style="color: {{ $chsColor }}">{{ number_format($metrics['meta']['chs'] ?? 0, 2) }}</div>
            </div>

            {{-- Critical Four --}}
            @foreach($metrics['critical_four'] ?? [] as $key => $value)
            <div class="text-center px-3">
                <div class="text-gray-400 text-xs">{{ $key }}</div>
                @php
                    $kColor = match($key) {
                        'SSI' => $value > 1.0 ? '#ff4444' : ($value > 0.8 ? '#ffcc00' : '#00ff88'),
                        'DI' => $value < 0.3 ? '#ff4444' : ($value < 0.5 ? '#ffcc00' : '#00ff88'),
                        'CF' => $value > 0.06 ? '#ff4444' : ($value > 0.03 ? '#ffcc00' : '#00ff88'),
                        'HBR' => $value > 0.25 ? '#ff4444' : ($value > 0.15 ? '#ffcc00' : '#00ff88'),
                        default => '#00ff88'
                    };
                @endphp
                <div class="font-bold text-lg" style="color: {{ $kColor }}">{{ number_format($value, 3) }}</div>
            </div>
            @endforeach

            {{-- Narrative Archetype --}}
            <div class="text-center px-3 border-l border-gray-700">
                <div class="text-gray-400 text-xs">ARCHETYPE</div>
                @php
                    $arch = $thermo['archetype'] ?? 'neutral';
                    $archColor = match($arch) {
                        'golden_age' => 'text-yellow-400',
                        'collapse' => 'text-red-500',
                        'turbulence' => 'text-orange-400',
                        'stagnation' => 'text-gray-400',
                        'neutral' => 'text-blue-300',
                        default => 'text-gray-500'
                    };
                @endphp
                <div class="font-bold text-sm uppercase {{ $archColor }}">
                    {{ str_replace('_', ' ', $arch) }}
                </div>
            </div>

            {{-- Resilience & Strain --}}
            <div class="flex flex-col gap-1 px-3 border-l border-gray-700 min-w-[120px]">
                {{-- Resilience --}}
                <div class="flex justify-between text-[10px] text-gray-400">
                    <span>RES</span>
                    <span>{{ number_format(($thermo['resilience'] ?? 0) * 100, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-1.5">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ ($thermo['resilience'] ?? 0) * 100 }}%"></div>
                </div>

                {{-- Strain --}}
                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                    <span>STR</span>
                    <span>{{ number_format(($thermo['strain'] ?? 0) * 100, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-1.5">
                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ ($thermo['strain'] ?? 0) * 100 }}%"></div>
                </div>
            </div>

            {{-- Severity Badge --}}
            <div class="text-center px-3 border-l border-gray-700">
                @php
                    $sevClass = match($metrics['severity'] ?? 'HEALTHY') {
                        'CRITICAL' => 'bg-red-500/20 text-red-400 ring-red-500/30',
                        'WARNING' => 'bg-yellow-500/20 text-yellow-400 ring-yellow-500/30',
                        default => 'bg-green-500/20 text-green-400 ring-green-500/30',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-bold ring-1 ring-inset {{ $sevClass }}">
                    {{ $metrics['severity'] ?? 'HEALTHY' }}
                </span>
            </div>
        </div>
    </div>

    {{-- NEW: Attractor Memory Section --}}
    @if(isset($attractorMemory))
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center text-white">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
            </svg>
            Attractor Memory
        </h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Current Attractor Info --}}
            <div>
                <div class="text-sm text-gray-400 mb-2">Current Cosmic Regime</div>
                <div class="flex items-center space-x-3 mb-3">
                    <span class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-base font-bold shadow-lg">
                        {{ $attractorMemory['code'] ?? 'EQUILIBRIUM' }}
                    </span>
                    @if($attractorMemory['is_morphing'] ?? false)
                        <span class="px-3 py-1 bg-yellow-600 text-white rounded-md text-xs font-semibold animate-pulse shadow-md">
                            🔄 Morphing...
                        </span>
                    @endif
                </div>
                
                @if($attractorMemory['incarnation_id'] ?? false)
                    <div class="text-xs text-gray-500 mt-2 font-mono bg-gray-900 px-2 py-1 rounded">
                        Incarnation: {{ substr($attractorMemory['incarnation_id'], 0, 8) }}...
                    </div>
                @endif
            </div>
            
            {{-- Morphing Progress --}}
            @if($attractorMemory['is_morphing'] ?? false)
            <div>
                <div class="text-sm text-gray-400 mb-2">Transition Progress</div>
                <div class="w-full bg-gray-700 rounded-full h-6 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 h-6 rounded-full transition-all duration-500 flex items-center justify-center text-white text-xs font-bold"
                         style="width: {{ $attractorMemory['morph_progress'] ?? 0 }}%">
                        {{ number_format($attractorMemory['morph_progress'] ?? 0, 1) }}%
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    Oscillatory morph in progress (damped spring dynamics)
                </div>
            </div>
            @endif
        </div>
        
        {{-- Incarnation Tree --}}
        @if(!empty($incarnationTree))
        <div class="mt-6 border-t border-gray-700 pt-5">
            <div class="text-sm font-semibold text-gray-300 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                Evolutionary Lineage ({{ count($incarnationTree) }} incarnation{{ count($incarnationTree) > 1 ? 's' : '' }})
            </div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($incarnationTree as $inc)
                <div class="flex items-center space-x-3 text-sm p-3 rounded-lg transition-colors
                            {{ $inc['is_active'] ? 'bg-gradient-to-r from-gray-700 to-gray-800 border border-green-500/30' : 'bg-gray-900 hover:bg-gray-800' }}">
                    <div class="w-3 h-3 rounded-full flex-shrink-0 {{ $inc['is_active'] ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-gray-600' }}"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-400 font-mono">
                                Tick {{ number_format($inc['start_tick']) }}
                            </span>
                            <span class="text-gray-600">→</span>
                            @if($inc['end_tick'] ?? false)
                                <span class="text-xs text-gray-400 font-mono">{{ number_format($inc['end_tick']) }}</span>
                            @else
                                <span class="text-xs text-green-400 font-bold">Now</span>
                            @endif
                        </div>
                    </div>
                    @if(isset($inc['semantic']['theme']))
                        <span class="text-xs px-2 py-1 bg-purple-900/50 text-purple-300 rounded border border-purple-500/30">
                            {{ $inc['semantic']['theme'] }}
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Row 1: State Radar + Stability Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-white mb-3">📡 State Vector</h4>
            <canvas id="stateRadar" height="250"></canvas>
        </div>
        <div class="lg:col-span-2 bg-gray-800 rounded-lg border border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-white mb-3">📈 Stability & Entropy Trend</h4>
            <canvas id="stabilityChart" height="200"></canvas>
        </div>
    </div>

    {{-- Row 2: 6 KPI Groups --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $groups = [
                ['title' => '🛡️ Stability', 'data' => $metrics['stability'] ?? [], 'color' => 'red'],
                ['title' => '🌱 Evolution', 'data' => $metrics['evolution'] ?? [], 'color' => 'green'],
                ['title' => '⚔️ Power', 'data' => $metrics['power'] ?? [], 'color' => 'yellow'],
                ['title' => '🧠 Memory', 'data' => $metrics['memory'] ?? [], 'color' => 'cyan'],
                ['title' => '✨ Emergence', 'data' => $metrics['emergence'] ?? [], 'color' => 'indigo'],
                ['title' => '⚖️ Governance', 'data' => $metrics['governance'] ?? [], 'color' => 'gray'],
            ];
        @endphp

        @foreach($groups as $group)
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="px-3 py-2 text-xs font-bold text-white bg-{{ $group['color'] }}-900/50 border-b border-gray-700">
                {{ $group['title'] }}
            </div>
            <div class="p-3 space-y-1">
                @foreach($group['data'] as $key => $value)
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 text-[10px] uppercase">{{ $key }}</span>
                    <code class="font-bold text-white text-xs">{{ number_format($value, 3) }}</code>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Row 3: Alerts + Attractors --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Active Alerts --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
                <h4 class="text-sm font-semibold text-white">🚨 Active Alerts</h4>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ empty($alerts) ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400' }}">
                    {{ count($alerts) }}
                </span>
            </div>
            <div class="p-0">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Sev</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Code</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Value</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Threshold</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($alerts as $alert)
                        <tr>
                            <td class="px-4 py-2">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($alert['severity'] ?? 'LOW') { 'CRITICAL' => 'bg-red-900/50 text-red-400', 'HIGH' => 'bg-yellow-900/50 text-yellow-400', default => 'bg-blue-900/50 text-blue-400' } }}">
                                    {{ $alert['severity'] ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs font-bold text-white">{{ $alert['code'] ?? '' }}</td>
                            <td class="px-4 py-2"><code class="text-xs text-gray-300">{{ number_format($alert['metric_value'] ?? 0, 4) }}</code></td>
                            <td class="px-4 py-2 text-xs text-gray-400">{{ $alert['threshold'] ?? '' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">✅ No active alerts</td>
                        </tr>
                        @endforelse

                        @foreach($composites ?? [] as $comp)
                        <tr class="bg-red-900/10">
                            <td class="px-4 py-2"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-700 text-gray-300">META</span></td>
                            <td class="px-4 py-2 text-xs font-bold text-red-400">{{ $comp['code'] }}</td>
                            <td colspan="2" class="px-4 py-2 text-xs text-gray-300">{{ $comp['description'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Attractor Inspector --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-5 py-4 border-b border-gray-700">
                <h4 class="text-sm font-semibold text-white">🔭 Attractor Inspector</h4>
            </div>
            <div class="p-0">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Attractor</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Distance</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Equilibrium</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @foreach($attractors as $att)
                        <tr class="{{ $att['is_current'] ? 'bg-indigo-900/20' : '' }}">
                            <td class="px-4 py-2 text-xs font-bold text-white">
                                {{ $att['code'] }}
                                @if($att['is_current'])
                                    <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-[10px] bg-indigo-500/20 text-indigo-400">ACTIVE</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @php $distColor = $att['distance_to_current'] < 0.2 ? 'text-green-400' : ($att['distance_to_current'] < 0.5 ? 'text-yellow-400' : 'text-gray-400'); @endphp
                                <code class="text-xs {{ $distColor }}">{{ number_format($att['distance_to_current'], 3) }}</code>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-400 font-mono">
                                E={{ $att['equilibrium']['entropy'] ?? '?' }} S={{ $att['equilibrium']['stability'] ?? '?' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Trajectory Chart --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
        <h4 class="text-sm font-semibold text-white mb-3">📊 Cosmic Trajectory</h4>
        <canvas id="trajectoryChart" height="150"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// State Radar
new Chart(document.getElementById('stateRadar'), {
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
        scales: { r: { min: 0, max: 1, ticks: { stepSize: 0.2 }, grid: { color: 'rgba(255,255,255,0.1)' } } },
        plugins: { legend: { display: false } }
    }
});

// Stability & Entropy Trend
new Chart(document.getElementById('stabilityChart'), {
    type: 'line',
    data: {
        labels: @json($trajectoryLabels),
        datasets: [
            { label: 'Entropy', data: @json($trajectoryEntropy), borderColor: '#ff6b6b', backgroundColor: 'rgba(255,107,107,0.1)', fill: true, tension: 0.3 },
            { label: 'Stability', data: @json($trajectoryStability), borderColor: '#00ff88', backgroundColor: 'rgba(0,255,136,0.1)', fill: true, tension: 0.3 },
            { label: 'Strain', data: @json($trajectoryStrain), borderColor: '#ffd93d', fill: false, tension: 0.3 },
        ]
    },
    options: { responsive: true, scales: { y: { min: 0, max: 1 } }, plugins: { legend: { position: 'top' } } }
});

// Trajectory
new Chart(document.getElementById('trajectoryChart'), {
    type: 'line',
    data: {
        labels: @json($trajectoryLabels),
        datasets: [
            { label: 'Energy', data: @json($trajectoryEnergy), borderColor: '#4ecdc4', tension: 0.3 },
            { label: 'Entropy', data: @json($trajectoryEntropy), borderColor: '#ff6b6b', tension: 0.3 },
        ]
    },
    options: { responsive: true, scales: { y: { min: 0, max: 1 } }, plugins: { legend: { position: 'top' } } }
});
</script>
@endpush
