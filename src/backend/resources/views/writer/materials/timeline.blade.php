@extends('layouts.writer')

@section('title', 'Dòng thời gian Vật chất - ' . $world->name)

@section('content')
<div class="space-y-8 pb-12 text-gray-100">
    <!-- Header -->
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('writer.sagas.index') }}" class="text-gray-500 hover:text-gray-300 transition-colors text-xs uppercase tracking-widest leading-none">Saga</a>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="h-4 w-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                        </svg>
                        <a href="{{ route('writer.materials.state-viewer', $world->id) }}" class="text-gray-500 hover:text-gray-300 transition-colors text-xs uppercase tracking-widest leading-none">Vật chất</a>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="h-4 w-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                        </svg>
                        <span class="text-indigo-400 text-xs uppercase tracking-widest font-semibold leading-none">Biến động</span>
                    </li>
                </ol>
            </nav>
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Lịch sử Biến động Vật chất</h2>
        </div>
        <div>
            <a href="{{ route('writer.materials.state-viewer', $world->id) }}" 
               class="inline-flex items-center rounded-lg bg-white/5 px-4 py-3 text-sm font-semibold text-gray-300 hover:bg-white/10 transition-all active:scale-95 leading-none border border-white/10">
                <svg class="-ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại Quản lý
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="rounded-2xl bg-gray-800/40 border border-white/10 backdrop-blur-xl p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2 leading-none">Loại Sự kiện</label>
                <select id="eventTypeFilter" class="w-full bg-gray-900/50 border border-white/5 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="all">Tất cả</option>
                    <option value="activation">Kích hoạt</option>
                    <option value="mutation">Đột biến</option>
                    <option value="deactivation">Thoái hóa</option>
                    <option value="strength_change">Thay đổi Cường độ</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2 leading-none">Vật chất</label>
                <select id="materialFilter" class="w-full bg-gray-900/50 border border-white/5 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="all">Tất cả</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->code }}">{{ $material->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2 leading-none">Epoch Bắt đầu</label>
                <input type="number" id="epochStart" value="0" class="w-full bg-gray-900/50 border border-white/5 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2 leading-none">Epoch Kết thúc</label>
                <input type="number" id="epochEnd" value="{{ $world->tick }}" class="w-full bg-gray-900/50 border border-white/5 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
        </div>
    </div>

    <!-- Timeline Content -->
    <div class="relative">
        <!-- Vertical Line -->
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-800 hidden md:block" aria-hidden="true"></div>

        <div class="space-y-6">
            @forelse($events as $event)
                <div class="timeline-item relative pl-0 md:pl-12 group" 
                     data-type="{{ $event['type'] }}" 
                     data-material="{{ $event['material_code'] }}" 
                     data-epoch="{{ $event['epoch'] }}">
                    <!-- Marker -->
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 hidden md:flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 border-2 {{ $event['type'] === 'activation' ? 'border-green-500 shadow-[0_0_10px_rgba(34,197,94,0.3)]' : ($event['type'] === 'mutation' ? 'border-yellow-500 shadow-[0_0_10px_rgba(234,179,8,0.3)]' : 'border-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.3)]') }} z-10 transition-transform group-hover:scale-110">
                        <div class="h-2 w-2 rounded-full {{ $event['type'] === 'activation' ? 'bg-green-500' : ($event['type'] === 'mutation' ? 'bg-yellow-500' : 'bg-indigo-500') }}"></div>
                    </div>

                    <!-- Content Card -->
                    <div class="rounded-2xl bg-gray-800/20 border border-white/5 p-5 backdrop-blur-sm transition-all hover:bg-gray-800/30 hover:border-white/10">
                        <div class="flex flex-col space-y-3 sm:flex-row sm:items-start sm:justify-between sm:space-y-0">
                            <div class="flex items-center space-x-3">
                                <span class="rounded bg-indigo-500/10 px-2 py-1 text-[10px] font-bold text-indigo-400 border border-indigo-500/20 leading-none">
                                    EPOCH {{ $event['epoch'] }}
                                </span>
                                <code class="text-sm font-bold text-white tracking-widest uppercase">{{ $event['material_code'] }}</code>
                            </div>
                            <time class="text-[10px] font-medium text-gray-500 uppercase tracking-widest">{{ $event['timestamp'] }}</time>
                        </div>
                        
                        <p class="mt-3 text-sm text-gray-300 leading-relaxed">{{ $event['description'] }}</p>
                        
                        <!-- Event Specific Details -->
                        @if($event['type'] === 'mutation')
                            <div class="mt-4 p-4 rounded-xl bg-yellow-500/5 border border-yellow-500/10 flex items-center space-x-4">
                                <div class="flex-shrink-0 text-yellow-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-yellow-600 uppercase tracking-[0.1em] mb-1">Tiến trình đột biến</div>
                                    <div class="text-xs text-yellow-200/80">
                                        <span class="font-bold text-yellow-500">{{ $event['from'] }}</span> 
                                        <span class="mx-2 text-gray-600">&rarr;</span> 
                                        <span class="font-bold text-yellow-500">{{ $event['to'] }}</span>
                                    </div>
                                    <div class="mt-1 text-[9px] text-gray-500 italic">{{ $event['pathway'] }}</div>
                                </div>
                            </div>
                        @endif

                        @if($event['type'] === 'strength_change')
                            <div class="mt-4 flex items-center space-x-4">
                                <div class="flex-1 space-y-1">
                                    <div class="text-[9px] font-bold text-gray-600 uppercase tracking-widest">Từ cường độ: {{ $event['old_strength'] }}</div>
                                    <div class="h-1.5 w-full bg-gray-900 rounded-full overflow-hidden">
                                        <div class="h-full bg-gray-700 transition-all duration-1000" style="width: {{ $event['old_strength'] * 10 }}%"></div>
                                    </div>
                                </div>
                                <div class="text-indigo-500 animate-pulse">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest">Đến: {{ $event['new_strength'] }}</div>
                                    <div class="h-1.5 w-full bg-gray-900 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)] transition-all duration-1000" style="width: {{ $event['new_strength'] * 10 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-700 bg-gray-800/10">
                    <svg class="h-12 w-12 text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-400">Chưa có dòng thời gian</h3>
                    <p class="mt-1 text-sm text-gray-500">Mọi biến động vật chất sẽ được ghi lại tại đây.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Premium Filter Implementation
    const filters = {
        type: document.getElementById('eventTypeFilter'),
        material: document.getElementById('materialFilter'),
        start: document.getElementById('epochStart'),
        end: document.getElementById('epochEnd')
    };

    Object.values(filters).forEach(el => el.addEventListener('input', applyFilters));

    function applyFilters() {
        const config = {
            type: filters.type.value,
            material: filters.material.value,
            start: parseInt(filters.start.value) || 0,
            end: parseInt(filters.end.value) || 999999
        };

        document.querySelectorAll('.timeline-item').forEach(item => {
            const data = {
                type: item.dataset.type,
                material: item.dataset.material,
                epoch: parseInt(item.dataset.epoch)
            };

            const matches = 
                (config.type === 'all' || data.type === config.type) &&
                (config.material === 'all' || data.material === config.material) &&
                (data.epoch >= config.start && data.epoch <= config.end);

            if (matches) {
                item.classList.remove('hidden');
                // Subtle fade in effect
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.transition = 'opacity 300ms ease';
                    item.style.opacity = '1';
                }, 10);
            } else {
                item.classList.add('hidden');
            }
        });
    }
</script>
@endsection
