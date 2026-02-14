@extends('layouts.writer')

@section('title', 'Quản lý Vật chất - ' . $world->name)

@section('content')
<div class="space-y-8 pb-12 text-gray-100">
    <!-- Breadcrumb & Header -->
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
                        <a href="{{ route('writer.sagas.worlds.show', [$world->saga_id ?? 1, $world->sequence ?? 0]) }}" class="text-gray-500 hover:text-gray-300 transition-colors text-xs uppercase tracking-widest leading-none">{{ $world->name }}</a>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="h-4 w-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                        </svg>
                        <span class="text-indigo-400 text-xs uppercase tracking-widest font-semibold leading-none">Vật chất</span>
                    </li>
                </ol>
            </nav>
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Quản lý Vật chất Thế giới</h2>
        </div>
        <div class="flex space-x-4">
            <button onclick="document.getElementById('activateModal').classList.remove('hidden')" 
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-500 transition-all active:scale-95 leading-none">
                <svg class="-ml-0.5 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Kích hoạt Vật chất mới
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-500/10 border border-green-500/20 p-4 text-sm text-green-400 flex items-center">
            <svg class="h-5 w-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-4 text-sm text-red-400 flex items-center">
            <svg class="h-5 w-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Active Materials Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
        @forelse($instances->where('activation_epoch', '!=', null)->where('retired_at', null) as $instance)
            <div class="relative overflow-hidden rounded-2xl bg-gray-800/40 border border-white/10 backdrop-blur-xl p-6 transition-all hover:border-indigo-500/30 group">
                <!-- Background Accent Glow -->
                <div class="absolute -right-12 -top-12 h-24 w-24 rounded-full bg-indigo-500/10 blur-3xl group-hover:bg-indigo-500/20 transition-all leading-none"></div>
                
                <div class="relative flex flex-col h-full">
                    <div class="flex items-start justify-between mb-6 leading-none">
                        <div class="flex items-center space-x-3 leading-none">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 leading-none">
                                <span class="font-bold text-lg leading-none">{{ substr($instance->material->code, 0, 1) }}</span>
                            </div>
                            <div class="leading-none">
                                <h3 class="text-lg font-bold text-white tracking-tight leading-tight">{{ str_replace('_', ' ', $instance->material->code) }}</h3>
                                <p class="text-[10px] text-gray-500 font-mono tracking-tighter uppercase mt-1 leading-none">{{ $instance->material->code }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-1 leading-none">
                            <button onclick="document.getElementById('forceMutateModal{{ $instance->id }}').classList.remove('hidden')" 
                                    class="p-2 rounded-lg bg-gray-700/50 text-gray-400 hover:text-yellow-400 hover:bg-gray-700 transition-all leading-none" title="Đột biến">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('writer.materials.retire', $instance->id) }}" onsubmit="return confirm('Bạn có chắc muốn thu hồi quy luật vật chất này?')">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg bg-gray-700/50 text-gray-400 hover:text-red-400 hover:bg-gray-700 transition-all leading-none" title="Thu hồi">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Strength Indicator -->
                    <div class="space-y-2 mb-6 leading-none">
                        <div class="flex items-center justify-between text-sm leading-none">
                            <span class="text-gray-400 leading-none">Cường độ hiện tại</span>
                            <span class="font-bold text-indigo-400 leading-none">{{ $instance->strength_level }}/10</span>
                        </div>
                        <div class="relative h-2 w-full rounded-full bg-gray-700/50 overflow-hidden shadow-inner leading-none">
                            <div class="absolute inset-y-0 left-0 bg-indigo-500 rounded-full transition-all duration-1000 ease-out shadow-[0_0_8px_rgba(99,102,241,0.6)] leading-none" 
                                 style="width: {{ $instance->strength_level * 10 }}%"></div>
                        </div>
                        <div class="flex justify-end pt-1 leading-none">
                            <button onclick="document.getElementById('strengthModal{{ $instance->id }}').classList.remove('hidden')" 
                                    class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors uppercase tracking-widest leading-none">Hiệu chỉnh &rarr;</button>
                        </div>
                    </div>

                    <!-- Info Badges -->
                    <div class="mt-auto flex flex-wrap gap-2 pt-4 border-t border-white/5 leading-none">
                        <span class="inline-flex items-center rounded-md bg-blue-400/10 px-2 py-1 text-[10px] font-bold text-blue-400 ring-1 ring-inset ring-blue-400/30 uppercase tracking-wider leading-none">
                            {{ $instance->material->ontology->value }}
                        </span>
                        <span class="inline-flex items-center rounded-md bg-purple-400/10 px-2 py-1 text-[10px] font-bold text-purple-400 ring-1 ring-inset ring-purple-400/30 uppercase tracking-wider leading-none">
                            {{ $instance->material->function->value }}
                        </span>
                        <span class="inline-flex items-center rounded-md bg-gray-400/5 px-2 py-1 text-[10px] font-bold text-gray-500 ring-1 ring-inset ring-white/10 uppercase tracking-wider leading-none">
                            Kích hoạt: Epoch {{ $instance->activation_epoch }}
                        </span>
                    </div>
                </div>
            </div>

            @include('writer.materials.partials.modals', ['instance' => $instance])

        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-700 bg-gray-800/10 leading-none">
                <svg class="h-12 w-12 text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.346a6 6 0 01-3.86.517l-2.388-.477a2 2 0 00-1.022.547l-1.168 1.168a2 2 0 01-1.788.707l-2.077-.346a2 2 0 00-1.405.516V20a2 2 0 002 2h14a2 2 0 002-2v-3.03a2 2 0 00-.516-1.405l-.347-2.077z" />
                </svg>
                <h3 class="text-sm font-medium text-gray-400 leading-none">Chưa có vật chất hoạt động</h3>
                <p class="mt-1 text-sm text-gray-500 leading-none">Hãy kích hoạt quy luật vật chất đầu tiên cho thế giới này.</p>
            </div>
        @endforelse
    </div>

    <!-- Secondary Sections -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 leading-none">
        <!-- Dormant Materials -->
        <div class="rounded-2xl bg-gray-800/20 border border-white/5 p-6 backdrop-blur-sm leading-none">
            <h3 class="text-lg font-bold text-gray-200 mb-4 flex items-center leading-none">
                <span class="h-2 w-2 rounded-full bg-gray-500 mr-2 leading-none"></span>
                Vật chất Tiềm ẩn
            </h3>
            <div class="flex flex-wrap gap-2 leading-none">
                @forelse($instances->where('activation_epoch', null) as $instance)
                    <span class="inline-flex items-center rounded-lg bg-gray-700/30 px-3 py-2 text-[10px] font-mono text-gray-400 border border-white/5 uppercase leading-none">
                        {{ $instance->material->code }}
                    </span>
                @empty
                    <p class="text-sm text-gray-600 italic px-2 leading-none">Không có vật chất nào đang ở trạng thái ngủ.</p>
                @endforelse
            </div>
        </div>

        <!-- Retired Materials -->
        <div class="rounded-2xl bg-gray-800/20 border border-white/5 p-6 backdrop-blur-sm leading-none">
            <h3 class="text-lg font-bold text-gray-200 mb-4 flex items-center leading-none">
                <span class="h-2 w-2 rounded-full bg-red-500 mr-2 animate-pulse leading-none"></span>
                Vật chất đã Thoái hóa
            </h3>
            <div class="space-y-3 leading-none">
                @forelse($instances->where('retired_at', '!=', null) as $instance)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-red-500/5 border border-red-500/10 leading-none">
                        <div class="flex items-center space-x-3 leading-none">
                            <span class="text-xs font-mono text-red-300 tracking-tighter uppercase leading-none">{{ $instance->material->code }}</span>
                            <span class="h-1 w-1 rounded-full bg-red-900 leading-none"></span>
                            <span class="text-[9px] text-gray-600 uppercase tracking-tighter leading-none">Thoái hóa: {{ $instance->retired_at->format('H:i d/m/Y') }}</span>
                        </div>
                        <span class="text-[9px] font-bold text-red-900 uppercase leading-none">Retired</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-600 italic px-2 leading-none">Chưa có quy luật nào bị phá bỏ.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Global Activation Modal -->
<div id="activateModal" class="hidden fixed inset-0 z-50 overflow-y-auto leading-none" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 leading-none">
        <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity leading-none" aria-hidden="true" onclick="document.getElementById('activateModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen leading-none" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10 leading-none">
            <form method="POST" action="{{ route('writer.materials.activate', $world->id) }}">
                @csrf
                <div class="px-6 py-6 sm:px-8 leading-none">
                    <div class="flex items-center justify-between mb-6 leading-none">
                        <h3 class="text-xl font-bold text-white tracking-tight leading-none">Kích hoạt Quy luật Vật chất</h3>
                        <button type="button" onclick="document.getElementById('activateModal').classList.add('hidden')" class="text-gray-500 hover:text-white transition-colors leading-none">
                            <svg class="h-6 w-6 text-gray-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <div class="space-y-6 leading-none">
                        <div class="leading-none">
                            <label class="block text-sm font-semibold text-gray-400 uppercase tracking-widest mb-2 leading-none">Loại Vật chất</label>
                            <select name="material_id" class="w-full bg-gray-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none leading-none" required>
                                <option value="">-- Chọn vật chất -- </option>
                                @foreach($availableMaterials as $material)
                                    <option value="{{ $material->id }}">{{ $material->code }} ({{ $material->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="leading-none">
                            <label class="block text-sm font-semibold text-gray-400 uppercase tracking-widest mb-2 leading-none">Cường độ Khởi tạo (1-10)</label>
                            <input type="range" name="strength_level" class="w-full h-2 bg-gray-900 rounded-lg appearance-none cursor-pointer accent-indigo-500 mb-2 leading-none" value="5" min="1" max="10" 
                                   oninput="this.nextElementSibling.value = this.value" required>
                            <output class="block text-center text-2xl font-black text-indigo-400 leading-none">5</output>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-900/50 px-6 py-4 sm:px-8 flex flex-row-reverse space-x-reverse space-x-3 leading-none">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-500 transition-all active:scale-95 leading-none">
                        Xác nhận Kích hoạt
                    </button>
                    <button type="button" onclick="document.getElementById('activateModal').classList.add('hidden')" 
                            class="inline-flex items-center justify-center rounded-xl bg-white/5 px-6 py-2.5 text-sm font-bold text-gray-400 hover:bg-white/10 hover:text-white transition-all leading-none">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Custom Slider Styling */
    input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #6366f1;
        cursor: pointer;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
    }
</style>
@endpush
