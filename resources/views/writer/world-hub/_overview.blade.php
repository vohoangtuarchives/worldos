{{-- OVERVIEW TAB --}}
<div class="space-y-6">
    {{-- Writer State Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Stability</dt>
            <dd class="mt-1 text-xl font-bold {{ ($writerState['stability'] ?? '') === 'Golden Age' ? 'text-green-400' : (($writerState['stability'] ?? '') === 'Crisis' ? 'text-red-400' : 'text-yellow-400') }}">
                {{ $writerState['stability'] ?? 'N/A' }}
            </dd>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Căng thẳng</dt>
            <dd class="mt-1 text-xl font-bold text-white">{{ $writerState['tension'] ?? 'N/A' }}</dd>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <dt class="text-sm font-medium text-gray-400">Reality Drift</dt>
            <dd class="mt-1 text-xl font-bold text-indigo-400 font-mono">{{ number_format($realityDrift * 100, 1) }}%</dd>
            <div class="mt-2 h-2 w-full bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500" style="width: {{ min($realityDrift * 100, 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Active Themes --}}
    @if(!empty($writerState['themes']))
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-base font-semibold text-white">🎭 Active Themes (Archetypes)</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($writerState['themes'] as $theme)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-700/30 border border-gray-700">
                    <div>
                        <div class="text-sm font-medium text-gray-200">{{ $theme['name'] }}</div>
                        <span class="text-xs font-medium {{ $theme['trend'] === 'Rising Intensity' ? 'text-green-400' : ($theme['trend'] === 'Fading Influence' ? 'text-red-400' : 'text-gray-400') }}">
                            {{ $theme['trend'] }}
                        </span>
                    </div>
                    <span class="text-lg font-bold text-white font-mono">{{ $theme['intensity'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Cosmic Rifts --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-base font-semibold text-white">🌀 Cosmic Rifts (Vết nứt Đa vũ trụ)</h3>
        </div>
        <div class="p-5">
            @forelse($gates as $gate)
                @php
                    $isOutgoing = $gate->source_world_id === $world->id;
                    $partner = $isOutgoing ? $gate->targetWorld : $gate->sourceWorld;
                @endphp
                <div class="flex items-center justify-between p-4 rounded-lg bg-gray-700/20 border border-indigo-500/30 mb-3 last:mb-0">
                    <div class="flex items-center">
                        <span class="text-lg {{ $isOutgoing ? 'text-pink-400' : 'text-indigo-400' }} animate-pulse">🌀</span>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-white">{{ $partner->name ?? 'Unknown' }}</p>
                            <p class="text-[10px] text-gray-500 uppercase">{{ $isOutgoing ? 'Invasion Source' : 'Under Invasion' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-mono text-indigo-300">{{ number_format($gate->throughput, 0) }} E/s</span>
                        <div class="mt-1 h-1 w-12 bg-gray-700 rounded-full">
                            <div class="h-full bg-indigo-500" style="width: {{ $gate->stability * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-sm text-gray-500 italic">"Thực tại vẫn đang ổn định... tạm thời."</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Chronicles --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-base font-semibold text-white">📜 Biên niên sử</h3>
        </div>
        <div class="divide-y divide-gray-700">
            @forelse($chronicles as $chronicle)
                <div class="px-5 py-4 sm:grid sm:grid-cols-4 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-400 font-mono">Kỷ nguyên {{ $chronicle->epoch }}</dt>
                    <dd class="mt-1 text-sm text-gray-100 sm:col-span-3 sm:mt-0">{{ $chronicle->content }}</dd>
                </div>
            @empty
                <div class="px-5 py-10 text-center">
                    <p class="text-sm text-gray-500">Chưa có biên niên sử. Thế giới chưa bắt đầu mô phỏng.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
