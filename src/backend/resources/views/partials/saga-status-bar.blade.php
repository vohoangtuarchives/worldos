@php
    $runningSaga = \App\Domains\Saga\Saga::where('status', 'running')->first();
@endphp

@if($runningSaga)
    <div x-data="{ openWorlds: false }" class="fixed bottom-0 left-64 right-0 z-50">
        
        <!-- Expanded World Drawer (Click to open) -->
        <div x-show="openWorlds" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-10"
             class="absolute bottom-full left-0 right-0 bg-gray-900 border-t border-gray-700 shadow-2xl p-4 max-h-96 overflow-y-auto">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Active Simulations</h3>
                <button @click="openWorlds = false" class="text-gray-500 hover:text-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($runningSaga->getActiveWorlds() as $w)
                    <a href="{{ $w->world_id ? route('writer.worlds.hub', $w->world_id) : '#' }}" 
                       class="block bg-gray-800 rounded border border-gray-700 p-3 hover:border-indigo-500 hover:bg-gray-750 transition group">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-indigo-400 text-xs font-mono mb-1">World #{{ $w->sequence }}</div>
                                <div class="text-white font-medium text-sm group-hover:text-indigo-300">
                                    {{ $w->world ? $w->world->name : 'Constructing...' }}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $w->status === 'running' ? 'bg-green-900/50 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                                {{ $w->status }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Main Status Bar -->
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 border-t border-indigo-500/30 shadow-[0_-5px_20px_rgba(99,102,241,0.15)] backdrop-blur-sm relative">
            <div class="px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <!-- Status Indicator -->
                    <div class="flex items-center gap-2 cursor-pointer" @click="openWorlds = !openWorlds">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                        </span>
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase animate-pulse">Running</span>
                        
                        <!-- Count Badge -->
                        @php $activeCount = $runningSaga->getActiveWorlds()->count(); @endphp
                        @if($activeCount > 1)
                            <span class="ml-1 px-1.5 py-0.5 bg-indigo-900/50 border border-indigo-500/30 text-indigo-300 text-[10px] rounded-full">
                                {{ $activeCount }} Worlds
                            </span>
                        @endif
                    </div>

                    <!-- Saga Info -->
                    <div class="flex items-center gap-4 border-l border-gray-700 pl-4">
                        <a href="{{ route('writer.sagas.show', $runningSaga->id) }}" class="group flex flex-col">
                            <span class="text-[10px] uppercase tracking-wider text-gray-500 group-hover:text-indigo-300 transition-colors">Saga</span>
                            <span class="text-sm font-bold text-white group-hover:text-indigo-400 transition-colors">{{ $runningSaga->name }}</span>
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button @click="openWorlds = !openWorlds" class="text-xs text-gray-400 hover:text-white flex items-center gap-1 mr-2 focus:outline-none">
                        <span x-text="openWorlds ? 'Hide Worlds' : 'Show Active Worlds'"></span>
                        <svg class="w-3 h-3 transition-transform" :class="{'rotate-180': openWorlds}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>

                    <a href="{{ route('writer.sagas.show', $runningSaga->id) }}" class="inline-flex items-center px-3 py-1.5 border border-indigo-500/50 rounded-md text-xs font-medium text-indigo-300 hover:bg-indigo-500/10 hover:text-white transition-colors">
                        Details
                    </a>
                </div>
            </div>
            
            <!-- Progress Bar Line -->
            @php
                $progressPercent = $runningSaga->world_count > 0 
                    ? ($runningSaga->current_world_index / $runningSaga->world_count) * 100 
                    : 0;
            @endphp
            <div class="absolute top-0 left-0 h-[1px] bg-indigo-500/30 w-full overflow-hidden">
                <div class="h-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)] transition-all duration-1000 ease-in-out" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
    </div>
@endif
