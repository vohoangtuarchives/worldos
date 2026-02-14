{{-- SOCIAL STRUCTURE TAB --}}
<div class="space-y-6">
    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $totalPower = collect($socialClasses)->sum('power');
            $avgContentment = collect($socialClasses)->avg('contentment');
            $largestClass = collect($socialClasses)->sortByDesc('size')->first();
        @endphp
        
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Quyền lực Tập trung</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-white">{{ number_format($totalPower, 2) }}</dd>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Hài lòng Trung bình</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-400">{{ number_format($avgContentment * 100, 1) }}%</dd>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Giai cấp Chủ đạo</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-400">
                {{ $largestClass ? str_replace('_', ' ', $largestClass['type']) : 'N/A' }}
            </dd>
        </div>
    </div>

    {{-- Class List --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
                <h4 class="text-sm font-semibold text-white">📡 Trạng thái Giai cấp</h4>
            </div>
            <div class="p-5 space-y-6">
                @foreach($socialClasses as $class)
                <div class="space-y-2">
                    <div class="flex justify-between items-end">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">
                                @switch($class['type'])
                                    @case('NOBILITY') 👑 @break
                                    @case('PRIESTHOOD') ⛪ @break
                                    @case('MERCHANT') 💰 @break
                                    @case('WARRIOR') ⚔️ @break
                                    @case('PEASANTRY') 🌾 @break
                                    @case('INTELLECTUAL') 🧠 @break
                                    @default 👤
                                @endswitch
                            </span>
                            <span class="font-bold text-white uppercase text-sm tracking-wider">{{ str_replace('_', ' ', $class['type']) }}</span>
                        </div>
                        <span class="text-[10px] text-gray-500 font-mono">SIZE: {{ number_format($class['size'] * 100, 1) }}%</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Power Bar --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-[10px] text-gray-400">
                                <span>POWER</span>
                                <span>{{ number_format($class['power'], 3) }}</span>
                            </div>
                            <div class="w-full bg-gray-900 rounded-full h-2 ring-1 ring-gray-700">
                                <div class="bg-indigo-500 h-2 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.5)]" style="width: {{ $class['power'] * 100 }}%"></div>
                            </div>
                        </div>
                        {{-- Contentment Bar --}}
                        <div class="space-y-1">
                            <div class="flex justify-between text-[10px] text-gray-400">
                                <span>CONTENTMENT</span>
                                <span>{{ number_format($class['contentment'] * 100, 0) }}%</span>
                            </div>
                            @php
                                $cColor = $class['contentment'] < 0.3 ? 'bg-red-500' : ($class['contentment'] < 0.6 ? 'bg-yellow-500' : 'bg-green-500');
                                $cShadow = $class['contentment'] < 0.3 ? 'rgba(239,68,68,0.5)' : ($class['contentment'] < 0.6 ? 'rgba(234,179,8,0.5)' : 'rgba(34,197,94,0.5)');
                            @endphp
                            <div class="w-full bg-gray-900 rounded-full h-2 ring-1 ring-gray-700">
                                <div class="{{ $cColor }} h-2 rounded-full" style="width: {{ $class['contentment'] * 100 }}%; box-shadow: 0 0 8px {{ $cShadow }};"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-white mb-4">📈 Lịch sử Biến động Xã hội</h4>
            <canvas id="socialTrendChart" height="300"></canvas>
            <div class="mt-4 text-[10px] text-gray-500 italic">
                * Dữ liệu 20 epoch gần nhất. Quyền lực giai cấp phản ánh khả năng tác động đến các chỉ số Cosmic.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('socialTrendChart').getContext('2d');
    const history = @json($socialHistory);
    
    // Process labels
    const labels = history.map(h => h.year);
    
    // Process datasets per class type
    const classTypes = ['NOBILITY', 'PRIESTHOOD', 'MERCHANT', 'WARRIOR', 'PEASANTRY', 'INTELLECTUAL'];
    const colors = {
        'NOBILITY': '#f59e0b',
        'PRIESTHOOD': '#ec4899',
        'MERCHANT': '#10b981',
        'WARRIOR': '#ef4444',
        'PEASANTRY': '#84cc16',
        'INTELLECTUAL': '#06b6d4'
    };

    const datasets = classTypes.map(type => {
        return {
            label: type,
            data: history.map(h => {
                const c = h.classes.find(cl => cl.type === type);
                return c ? c.power : 0;
            }),
            borderColor: colors[type],
            backgroundColor: `${colors[type]}22`,
            borderWidth: 2,
            pointRadius: 0,
            tension: 0.3,
            fill: false
        };
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#334155' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#cbd5e1', boxWidth: 10, padding: 15, font: { size: 10 } }
                }
            }
        }
    });
});
</script>
@endpush
