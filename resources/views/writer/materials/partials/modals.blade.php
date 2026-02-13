{{-- Strength Adjustment Modal --}}
<div id="strengthModal{{ $instance->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('strengthModal{{ $instance->id }}').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10">
            <form method="POST" action="{{ route('writer.materials.adjust-strength', $instance->id) }}">
                @csrf
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white tracking-tight">Hiệu chỉnh Cường độ</h3>
                        <button type="button" onclick="document.getElementById('strengthModal{{ $instance->id }}').classList.add('hidden')" class="text-gray-500 hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-400">Điều chỉnh mức độ ảnh hưởng của <span class="text-indigo-400 font-mono">{{ $instance->material->code }}</span> lên thế giới.</p>
                        <div>
                            <label class="block text-sm font-semibold text-gray-400 uppercase tracking-widest mb-2">Cường độ (0-10)</label>
                            <input type="range" name="strength_level" class="w-full h-2 bg-gray-900 rounded-lg appearance-none cursor-pointer accent-indigo-500 mb-2" 
                                   value="{{ $instance->strength_level }}" min="0" max="10" 
                                   oninput="this.nextElementSibling.value = this.value" required>
                            <output class="block text-center text-2xl font-black text-indigo-400">{{ $instance->strength_level }}</output>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-900/50 px-6 py-4 sm:px-8 flex flex-row-reverse space-x-reverse space-x-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-indigo-500 transition-all active:scale-95">
                        Lưu thay đổi
                    </button>
                    <button type="button" onclick="document.getElementById('strengthModal{{ $instance->id }}').classList.add('hidden')" 
                            class="inline-flex items-center justify-center rounded-xl bg-white/5 px-6 py-2.5 text-sm font-bold text-gray-400 hover:bg-white/10">
                        Đóng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Mutation Modal --}}
<div id="forceMutateModal{{ $instance->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('forceMutateModal{{ $instance->id }}').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10">
            <form method="POST" action="{{ route('writer.materials.force-mutation', $instance->id) }}">
                @csrf
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white tracking-tight">Cưỡng bức Đột biến</h3>
                        <button type="button" onclick="document.getElementById('forceMutateModal{{ $instance->id }}').classList.add('hidden')" class="text-gray-500 hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                            <p class="text-xs text-yellow-500 font-medium">Cảnh báo: Đột biến cưỡng bức sẽ làm suy yếu cường độ của quy luật nguyên bản và tạo ra một biến thể mới.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-400 uppercase tracking-widest mb-2">Mã Vật chất Mục tiêu</label>
                            <input type="text" name="target_code" class="w-full bg-gray-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none font-mono" 
                                   placeholder="VD: THEOCRATIC_STATE" required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-900/50 px-6 py-4 sm:px-8 flex flex-row-reverse space-x-reverse space-x-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-yellow-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-yellow-500 transition-all active:scale-95">
                        Thực thi Đột biến
                    </button>
                    <button type="button" onclick="document.getElementById('forceMutateModal{{ $instance->id }}').classList.add('hidden')" 
                            class="inline-flex items-center justify-center rounded-xl bg-white/5 px-6 py-2.5 text-sm font-bold text-gray-400 hover:bg-white/10">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
