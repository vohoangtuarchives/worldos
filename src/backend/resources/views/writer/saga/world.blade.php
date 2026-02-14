@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <div>
                        <span class="sr-only">Trang chủ</span>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('writer.sagas.show', $saga) }}" class="ml-4 text-sm font-medium text-gray-200 hover:text-white">{{ $saga->name }}</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-400">Thế giới #{{ $sagaWorld->sequence + 1 }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                {{ $sagaWorld->world->name }}
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-400">
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $writerState['stability'] === 'Golden Age' ? 'bg-green-400/10 text-green-400 ring-green-400/20' : ($writerState['stability'] === 'Crisis' ? 'bg-red-400/10 text-red-400 ring-red-400/20' : 'bg-yellow-400/10 text-yellow-400 ring-yellow-400/20') }}">
                        {{ $writerState['stability'] }}
                    </span>
                    <span class="ml-2 border-l border-gray-700 pl-2">
                        Căng thẳng: {{ $writerState['tension'] }}
                    </span>
                    @php
                        $timeManager = app(\App\Domains\Time\TimeManager::class);
                    @endphp
                    <span class="ml-2 border-l border-gray-700 pl-2 text-indigo-400 font-mono">
                        {{ $timeManager->formatTime($sagaWorld->world->current_time, $sagaWorld->world->calendar_system) }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <a href="{{ route('writer.worlds.hub', $sagaWorld->world_id) }}" class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                Open World Hub
            </a>

            <a href="{{ route('writer.materials.state-viewer', $sagaWorld->world_id) }}" class="inline-flex items-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-white/20">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Quản lý Vật liệu
            </a>

            <form action="{{ route('writer.sagas.run', $saga) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Chạy Mô phỏng
                </button>
            </form>

            @if($story)
                <a href="{{ route('writer.story.show', $story) }}" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    Đọc Truyện
                </a>
            @elseif($chronicles->isNotEmpty())
                <form action="{{ route('writer.story.publish', $sagaWorld->world_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        Xuất bản Truyện
                    </button>
                </form>
            @endif
        </div>
        </div>
    </div>

    <!-- Autonomous Simulation Controls (New Engine) -->
    <div class="bg-gradient-to-r from-purple-900/40 to-indigo-900/40 rounded-lg border border-purple-500/30 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-2xl mr-3">🤖</span>
                <div>
                    <h3 class="text-base font-semibold text-white">Autonomous Engine v2</h3>
                    <p class="text-xs text-indigo-300">Hệ thống mô phỏng tự trị & tiến hóa dài hạn</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                 <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $sagaWorld->world->autonomous ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-gray-700 text-gray-400' }}">
                    {{ $sagaWorld->world->autonomous ? 'ACTIVE' : 'IDLE' }}
                </span>
                
                {{-- Toggle Button --}}
                <form method="POST" action="{{ route('writer.worlds.autonomous.toggle', $sagaWorld->world_id) }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 rounded-md px-3 py-1.5 text-sm font-semibold text-white transition-all shadow-sm
                        {{ $sagaWorld->world->autonomous 
                            ? 'bg-red-600 hover:bg-red-500 ring-1 ring-red-400' 
                            : 'bg-green-600 hover:bg-green-500 ring-1 ring-green-400' }}">
                        <span>{{ $sagaWorld->world->autonomous ? 'Stop' : 'Start' }}</span>
                    </button>
                </form>

                {{-- Manual Tick --}}
                <form method="POST" action="{{ route('writer.worlds.autonomous.tick', $sagaWorld->world_id) }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 transition-all shadow-sm ring-1 ring-indigo-400">
                        <span>⚡ Tick</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Material Monitor -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-base font-semibold leading-6 text-white">Material Monitor (Giám sát Vật liệu)</h3>
                <p class="mt-1 text-sm text-gray-400">Trạng thái các yếu tố nền tảng cấu thành thế giới.</p>
            </div>
            <span class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-600">
                {{ $materials->count() }} Active Elements
            </span>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($materials->groupBy('material.ontology') as $ontology => $items)
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-widest border-b border-gray-700 pb-2 mb-3">
                            {{ $ontology }}
                        </h4>
                        @foreach($items as $instance)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-700/30 border border-gray-700 hover:border-gray-600 transition-colors">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="text-sm font-medium text-gray-200 truncate" title="{{ $instance->material->description }}">
                                        {{ str_replace('_', ' ', $instance->material->code) }}
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <div class="h-1.5 flex-1 bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full {{ $instance->strength_level > 7 ? 'bg-green-500' : ($instance->strength_level < 3 ? 'bg-red-500' : 'bg-amber-500') }}" style="width: {{ ($instance->strength_level / 10) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold font-mono {{ $instance->strength_level > 7 ? 'text-green-400' : ($instance->strength_level < 3 ? 'text-red-400' : 'text-amber-400') }}">
                                        {{ $instance->strength_level }}
                                    </div>
                                    <div class="text-[10px] uppercase text-gray-500 mt-0.5">
                                        {{ isset($instance->mutation_state['original']) ? 'BASE' : 'MUTATED' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Cosmic Connectivity (Multiverse Automation) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Reality Drift Card -->
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700 transition hover:border-indigo-500/50">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-700">
                <h3 class="text-base font-semibold leading-6 text-white">Reality Drift (Trôi dạt Thực tại)</h3>
                <p class="mt-1 text-sm text-gray-400">Độ lệch của quy luật vật lý so với nguyên bản.</p>
            </div>
            <div class="px-4 py-5 sm:p-6 flex flex-col justify-center">
                <div class="flex items-end justify-between mb-2">
                    <span class="text-3xl font-bold text-white font-mono">{{ number_format($realityDrift * 100, 1) }}%</span>
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Physics Divergence</span>
                </div>
                <div class="h-4 w-full bg-gray-700 rounded-full overflow-hidden border border-gray-600">
                    <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 shadow-[0_0_10px_rgba(139,92,246,0.5)]" style="width: {{ $realityDrift * 100 }}%"></div>
                </div>
                <p class="mt-4 text-xs text-gray-400 leading-relaxed italic">
                    "Thực tại đang dần tan rã. Những quy luật cơ bản không còn giữ được hình hài nguyên thủy khi entropy từ các thế giới khác xâm chiếm."
                </p>
            </div>
        </div>

        <!-- Active Rifts Card -->
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-700">
                <h3 class="text-base font-semibold leading-6 text-white">Cosmic Rifts (Vết nứt Đa vũ trụ)</h3>
                <p class="mt-1 text-sm text-gray-400">Các kết nối đang hoạt động với các thế giới khác.</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @forelse($gates as $gate)
                    @php
                        $isOutgoing = $gate->source_world_id === $sagaWorld->world_id;
                        $partner = $isOutgoing ? $gate->targetWorld : $gate->sourceWorld;
                    @endphp
                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-700/20 border border-indigo-500/30 mb-3 last:mb-0">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($isOutgoing)
                                    <svg class="h-6 w-6 text-pink-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699-2.7a14.93 14.93 0 015.841-2.58m1.76 1.76a14.923 14.923 0 012.58 5.841m-2.58-5.84a14.926 14.926 0 00-5.841 2.58M14.24 14.24a14.932 14.932 0 01-5.841 2.58m-4.522-4.522a14.928 14.928 0 012.58-5.841m5.84 2.58a14.926 14.926 0 00-2.58 5.841" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 text-indigo-400 rotate-180 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699-2.7a14.93 14.93 0 015.841-2.58m1.76 1.76a14.923 14.923 0 012.58 5.841m-2.58-5.84a14.926 14.926 0 00-5.841 2.58M14.24 14.24a14.932 14.932 0 01-5.841 2.58m-4.522-4.522a14.928 14.928 0 012.58-5.841m5.84 2.58a14.926 14.926 0 00-2.58 5.841" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-bold text-white truncate">{{ $partner->name }}</p>
                                <p class="text-[10px] text-gray-500 uppercase tracking-tighter">{{ $isOutgoing ? 'Invasion Source' : 'Under Invasion' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-mono text-indigo-300">{{ number_format($gate->throughput, 0) }} E/s</span>
                            <div class="flex items-center mt-1">
                                <div class="h-1 w-12 bg-gray-700 rounded-full">
                                    <div class="h-full bg-indigo-500 shadow-[0_0_5px_rgba(99,102,241,0.5)]" style="width: {{ $gate->stability * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="h-10 w-10 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-xs text-gray-500 italic">"Thực tại vẫn đang ổn định... tạm thời."</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Themes (Archetypes) -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($writerState['themes'] as $theme)
            <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700 transition hover:border-indigo-500/50">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Dynamic icon based on trend -->
                            @if($theme['trend'] === 'Rising Intensity')
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>
                            @elseif($theme['trend'] === 'Fading Influence')
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-400">{{ $theme['name'] }}</dt>
                                <dd>
                                    <div class="text-lg font-medium text-white">{{ $theme['intensity'] }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800/50 px-5 py-3 border-t border-gray-700">
                    <div class="text-sm">
                        <span class="font-medium text-indigo-400">{{ $theme['trend'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Historical Events (Canon) -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-base font-semibold leading-6 text-white">Biên niên sử</h3>
            <p class="mt-1 text-sm text-gray-400">Những sự kiện quan trọng định hình kỷ nguyên này.</p>
        </div>
        <div class="border-t border-gray-700 px-4 py-5 sm:p-0">
            <dl class="divide-y divide-gray-700">
                @forelse($chronicles as $chronicle)
                    @php
                        // Calculate approximate time for this chronicle based on epoch if world_time not available
                        // Ideally chronicle should store world_time. For now, we estimate or use current world time if it's the latest.
                        // Actually, let's just show Epoch + Formatted estimation if possible, or just Epoch for now until Chronicles table is updated.
                        // BUT, the requirement is to show "World Date".
                        // Use TimeManager instance injected into view or facade.
                        $timeManager = app(\App\Domains\Time\TimeManager::class);
                        // Assuming chronicle epoch correlates to time roughly 1:1 in old system, but new system varies.
                        // We should probably migrate chronicles to have `world_time` too.
                        // For now, let's just display the Epoch as "Kỷ nguyên".
                        // Wait, the plan said "Update Dashboard to show formatted World Date".
                        // This view shows the LIST of chronicles.
                    @endphp
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-400">
                            Kỷ nguyên {{ $chronicle->epoch }}
                            {{-- <br>
                            <span class="text-xs text-gray-500">{{ $timeManager->formatTime($chronicle->epoch) }}</span> --}}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-100 sm:col-span-2 sm:mt-0">
                            {{ $chronicle->content }}
                        </dd>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-400">Chưa có biên niên sử</h3>
                        <p class="mt-1 text-sm text-gray-500">Thế giới này vẫn chưa bắt đầu chuyển động hoặc vẫn đang trong quá trình mô phỏng.</p>
                    </div>
                @endforelse
            </dl>
        </div>
    </div>
</div>
@endsection
