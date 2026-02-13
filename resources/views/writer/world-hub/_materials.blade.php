{{-- MATERIALS TAB --}}
<div class="space-y-6">
    {{-- Material Summary --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-base font-semibold text-white">⚗️ Material Monitor</h3>
                <p class="mt-1 text-sm text-gray-400">Trạng thái các yếu tố nền tảng cấu thành thế giới.</p>
            </div>
            <span class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-600">
                {{ $instances->count() }} Active
            </span>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($instances->groupBy(fn($i) => $i->material->ontology ?? 'Unknown') as $ontology => $items)
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-widest border-b border-gray-700 pb-2">
                            {{ $ontology }}
                        </h4>
                        @foreach($items as $instance)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-700/30 border border-gray-700 hover:border-gray-600 transition-colors">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="text-sm font-medium text-gray-200 truncate">{{ str_replace('_', ' ', $instance->material->code) }}</div>
                                    <div class="flex items-center mt-1">
                                        <div class="h-1.5 flex-1 bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full {{ $instance->strength_level > 7 ? 'bg-green-500' : ($instance->strength_level < 3 ? 'bg-red-500' : 'bg-amber-500') }}"
                                                 style="width: {{ ($instance->strength_level / 10) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold font-mono {{ $instance->strength_level > 7 ? 'text-green-400' : ($instance->strength_level < 3 ? 'text-red-400' : 'text-amber-400') }}">
                                        {{ $instance->strength_level }}
                                    </div>
                                    <div class="text-[10px] uppercase text-gray-500 mt-0.5">
                                        {{ $instance->status ?? 'ACTIVE' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Activate Material --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-white mb-4">🔋 Kích hoạt Material</h4>
            <form method="POST" action="{{ route('writer.materials.activate', $world->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Material</label>
                    <select name="material_id" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($availableMaterials as $mat)
                            <option value="{{ $mat->id }}">{{ str_replace('_', ' ', $mat->code) }} ({{ $mat->ontology }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Strength (1-10)</label>
                    <input type="number" name="initial_strength" value="5" min="1" max="10" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Kích hoạt
                </button>
            </form>
        </div>

        {{-- Material Timeline Link --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-white mb-4">📊 Công cụ khác</h4>
            <div class="space-y-3">
                <a href="{{ route('writer.materials.timeline', $world->id) }}"
                   class="block w-full text-center rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-200 hover:bg-gray-600 transition-colors">
                    📈 Xem Timeline Sự kiện
                </a>
                <a href="{{ route('writer.materials.state-viewer', $world->id) }}"
                   class="block w-full text-center rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-200 hover:bg-gray-600 transition-colors">
                    🔍 Mở State Viewer đầy đủ
                </a>
            </div>
        </div>
    </div>
</div>
