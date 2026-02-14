@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="text-center py-8">
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-500 to-red-600 tracking-tight">
            開天闢地
        </h1>
        <p class="mt-2 text-2xl font-semibold text-white">Khai Thiên Tịch Địa</p>
        <p class="mt-1 text-sm text-gray-400">Chọn một nguyên mẫu hoặc tự tạo tổ hợp riêng để sáng tạo thế giới</p>
    </div>

    <form action="{{ route('writer.genesis.store') }}" method="POST" id="genesis-form">
        @csrf

        <!-- Hidden fields for preset/origin data -->
        <input type="hidden" name="preset_key" id="preset_key" value="">
        <input type="hidden" name="origin_type" id="origin_type" value="cosmic">

        <!-- Origin Selector -->
        <div class="mb-10">
            <h2 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400 mb-6 border-b border-gray-700 pb-2 flex items-center gap-2">
                <span class="text-2xl">🌌</span> Bước 1: Chọn Nguồn Gốc (Origin)
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cosmic Origin -->
                <div class="origin-card group cursor-pointer relative overflow-hidden rounded-2xl border-2 border-indigo-500/50 bg-gray-900/80 p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-indigo-400 hover:shadow-indigo-500/20 active"
                     onclick="selectOrigin(this, 'cosmic')">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl transition-all group-hover:bg-indigo-500/20"></div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="rounded-lg bg-indigo-900/30 p-3 ring-1 ring-indigo-500/30">
                            <span class="text-3xl">🌌</span>
                        </div>
                        <div class="origin-check opacity-100 text-indigo-400">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-indigo-300 transition-colors">Vũ Trụ Nguyên Thủy</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Khởi đầu từ Hỗn Mang (Chaos), hình thành các Attractor vũ trụ và tiến hóa theo quy luật vật lý/siêu hình chuẩn.
                    </p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="px-2 py-1 rounded bg-gray-800 text-xs font-mono text-gray-300 border border-gray-700">Standard Physics</span>
                        <span class="px-2 py-1 rounded bg-gray-800 text-xs font-mono text-gray-300 border border-gray-700">Cosmic Attractors</span>
                    </div>
                </div>

                <!-- Vietnamese Origin -->
                <div class="origin-card group cursor-pointer relative overflow-hidden rounded-2xl border-2 border-gray-700 bg-gray-900/80 p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500 hover:shadow-red-500/20"
                     onclick="selectOrigin(this, 'vietnamese')">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-red-500/10 blur-3xl transition-all group-hover:bg-red-500/20"></div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="rounded-lg bg-red-900/30 p-3 ring-1 ring-red-500/30">
                            <span class="text-3xl">🇻🇳</span>
                        </div>
                        <div class="origin-check opacity-0 text-red-400 transition-opacity">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-red-300 transition-colors">Thần Thoại Việt Nam</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Khởi đầu từ Lạc Long Quân & Âu Cơ. Trăm Trứng nở trăm con. Thế giới phân tách thành Núi & Biển.
                    </p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="px-2 py-1 rounded bg-gray-800 text-xs font-mono text-gray-300 border border-gray-700">Trăm Trứng</span>
                        <span class="px-2 py-1 rounded bg-gray-800 text-xs font-mono text-gray-300 border border-gray-700">Hero Bifurcation</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 Header -->
        <h2 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400 mb-6 border-b border-gray-700 pb-2 flex items-center gap-2">
            <span class="text-2xl">🌍</span> Bước 2: Chọn Preset Thế Giới
        </h2>
        <!-- Other fields are now in the Mixing Panel -->

        <!-- Preset Cards by Category -->
        @foreach($categories as $catKey => $category)
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-200 mb-4 border-b border-gray-700 pb-2">
                {{ $category['label'] }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($category['presets'] as $preset)
                <div class="preset-card group cursor-pointer rounded-xl border-2 border-gray-700 bg-gray-800/80 p-5 transition-all duration-200 hover:border-amber-500 hover:shadow-lg hover:shadow-amber-500/10 hover:scale-[1.02]"
                     data-preset-key="{{ $preset['key'] }}"
                     data-genre="{{ $preset['genre'] }}"
                     data-power-system="{{ $preset['power_system'] }}"
                     data-power-ceiling="{{ $preset['power_ceiling'] }}"
                     data-tech-level="{{ $preset['tech_level'] }}"
                     data-environment="{{ $preset['environment'] }}"
                     data-social-structure="{{ $preset['social_structure'] }}"
                     data-starting-crisis="{{ $preset['starting_crisis'] }}"
                     data-power-ranking="{{ $preset['power_ranking'] }}"
                     onclick="selectPreset(this)">
                    <div class="flex items-start justify-between">
                        <span class="text-3xl">{{ $preset['icon'] }}</span>
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity text-xs text-amber-400 font-medium">Chọn</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-white group-hover:text-amber-300 transition-colors">
                        {{ $preset['name'] }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-400 leading-relaxed">
                        {{ $preset['description'] }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center rounded-full bg-gray-700/50 px-2 py-0.5 text-[10px] font-medium text-gray-300">
                            {{ $preset['genre'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-indigo-900/40 px-2 py-0.5 text-[10px] font-medium text-indigo-300">
                            {{ $preset['power_system'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-emerald-900/40 px-2 py-0.5 text-[10px] font-medium text-emerald-300">
                            {{ $preset['environment'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Mixing Panel / Config Display -->
        <div id="genesis-config" class="hidden mt-8 bg-gray-800 rounded-xl border border-amber-600/50 shadow-lg shadow-amber-500/5">
            <div class="px-6 py-5 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-amber-400" id="selected-preset-name">—</h3>
                        <p class="text-sm text-gray-400 mt-1" id="selected-preset-desc">Chọn một preset ở trên</p>
                    </div>
                    <span class="text-4xl" id="selected-preset-icon">🌍</span>
                </div>
            </div>

            <div class="px-6 py-5 space-y-6">
                <!-- Saga Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-200">Tên Saga <span class="text-red-400">*</span></label>
                    <input type="text" name="name" id="name" required
                           class="mt-2 block w-full rounded-lg border-0 bg-gray-900 py-2.5 px-4 text-white shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-500 focus:ring-2 focus:ring-amber-500 sm:text-sm"
                           placeholder="đặt tên cho thế giới của bạn...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="world_count" class="block text-sm font-medium text-gray-200">Số thế giới</label>
                        <input type="number" name="world_count" id="world_count" value="5" min="1" max="20"
                               class="mt-2 block w-full rounded-lg border-0 bg-gray-900 py-2.5 px-4 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="carry_legacy" class="block text-sm font-medium text-gray-200">Di sản</label>
                        <select name="carry_legacy" id="carry_legacy"
                                class="mt-2 block w-full rounded-lg border-0 bg-gray-900 py-2.5 px-4 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-sm">
                            <option value="1">Kế thừa di sản</option>
                            <option value="0">Mỗi thế giới độc lập</option>
                        </select>
                    </div>
                </div>

                <!-- Mixing Panel -->
                <div class="border border-gray-700 rounded-lg p-4 bg-gray-900/30">
                    <h4 class="text-sm font-semibold text-gray-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Mixing Panel (Tùy chỉnh cấu hình)
                    </h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Power System -->
                        <div>
                            <label for="field_power_system" class="block text-xs font-medium text-gray-500 mb-1">Hệ sức mạnh</label>
                            <select name="power_system" id="field_power_system" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($power_systems as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Power Ceiling -->
                        <div>
                            <label for="field_power_ceiling" class="block text-xs font-medium text-gray-500 mb-1">Trần sức mạnh</label>
                            <select name="power_ceiling" id="field_power_ceiling" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($power_ceilings as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Tech Level -->
                         <div>
                            <label for="field_tech_level" class="block text-xs font-medium text-gray-500 mb-1">Công nghệ</label>
                            <select name="tech_level" id="field_tech_level" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($tech_levels as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Power Ranking -->
                        <div>
                            <label for="field_power_ranking" class="block text-xs font-medium text-gray-500 mb-1">Hệ xếp hạng</label>
                            <select name="power_ranking" id="field_power_ranking" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($power_rankings as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Environment -->
                        <div>
                            <label for="field_environment" class="block text-xs font-medium text-gray-500 mb-1">Môi trường</label>
                            <select name="environment" id="field_environment" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($environments as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Social Structure -->
                        <div>
                            <label for="field_social_structure" class="block text-xs font-medium text-gray-500 mb-1">Cấu trúc xã hội</label>
                            <select name="social_structure" id="field_social_structure" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($social_structures as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Starting Crisis -->
                        <div>
                            <label for="field_starting_crisis" class="block text-xs font-medium text-gray-500 mb-1">Khủng hoảng đầu</label>
                            <select name="starting_crisis" id="field_starting_crisis" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs">
                                <option value="">Theo Preset</option>
                                @foreach($starting_crises as $val)
                                    <option value="{{ $val->value }}">{{ $val->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Genre (Hidden/Read-only mostly but let's allow change) -->
                         <div>
                            <label for="field_genre" class="block text-xs font-medium text-gray-500 mb-1">Genre Code</label>
                            <input type="text" name="genre" id="field_genre" class="block w-full rounded-md border-0 bg-gray-800 py-1.5 text-gray-400 shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-amber-500 sm:text-xs font-mono" readonly>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-700">
                    <button type="button" onclick="resetGenesis()" class="text-sm font-semibold text-gray-400 hover:text-white transition-colors">
                        Chọn lại
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/25 hover:from-amber-400 hover:to-orange-500 transition-all hover:shadow-amber-500/40 hover:scale-105">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        Khai Thiên Tịch Địa
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Recent Sagas -->
    @if($recent_sagas->count() > 0)
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-200 mb-4">Saga Gần Đây</h3>
        <div class="overflow-hidden bg-gray-800 shadow sm:rounded-md border border-gray-700">
            <ul role="list" class="divide-y divide-gray-700">
                @foreach($recent_sagas as $saga)
                <li>
                    <a href="{{ route('writer.sagas.show', $saga) }}" class="block hover:bg-gray-700/50">
                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-400">{{ $saga->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $saga->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-green-900/50 px-2 text-xs font-semibold leading-5 text-green-300 border border-green-800">
                                {{ ucfirst($saga->status) }}
                            </span>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>

<script>
function selectOrigin(card, type) {
    // Update hidden input
    document.getElementById('origin_type').value = type;

    // Visual update
    document.querySelectorAll('.origin-card').forEach(c => {
        c.classList.remove('active', 'border-indigo-400', 'border-red-500', 'ring-2', 'ring-offset-2');
        c.classList.add('border-gray-700');
        
        // Hide check icons
        const check = c.querySelector('.origin-check');
        if(check) {
            check.classList.remove('opacity-100');
            check.classList.add('opacity-0');
        }
    });

    // Active state
    card.classList.remove('border-gray-700');
    if (type === 'vietnamese') {
        card.classList.add('border-red-500', 'active');
    } else {
        card.classList.add('border-indigo-400', 'active');
    }

    // Show check icon
    const check = card.querySelector('.origin-check');
    if(check) {
        check.classList.remove('opacity-0');
        check.classList.add('opacity-100');
    }
}

function selectPreset(card) {
    // Remove selection from all
    document.querySelectorAll('.preset-card').forEach(c => {
        c.classList.remove('border-amber-500', 'bg-gray-700/50');
        c.classList.add('border-gray-700');
    });

    // Select this one
    card.classList.remove('border-gray-700');
    card.classList.add('border-amber-500', 'bg-gray-700/50');

    // Fill hidden fields
    document.getElementById('preset_key').value = card.dataset.presetKey;
    document.getElementById('field_genre').value = card.dataset.genre;
    document.getElementById('field_power_system').value = card.dataset.powerSystem;
    document.getElementById('field_power_ceiling').value = card.dataset.powerCeiling;
    document.getElementById('field_tech_level').value = card.dataset.techLevel;
    document.getElementById('field_environment').value = card.dataset.environment;
    document.getElementById('field_social_structure').value = card.dataset.socialStructure;
    document.getElementById('field_starting_crisis').value = card.dataset.startingCrisis;
    document.getElementById('field_power_ranking').value = card.dataset.powerRanking;

    // Update display
    const name = card.querySelector('h3').textContent.trim();
    const desc = card.querySelector('p').textContent.trim();
    const icon = card.querySelector('.text-3xl').textContent.trim();

    document.getElementById('selected-preset-name').textContent = name;
    document.getElementById('selected-preset-desc').textContent = desc;
    document.getElementById('selected-preset-icon').textContent = icon;

    // Config display - Now handled by form values above

    // Show config panel
    document.getElementById('genesis-config').classList.remove('hidden');

    // Auto-fill saga name
    const nameInput = document.getElementById('name');
    if (!nameInput.value) {
        nameInput.value = name;
    }

    // Scroll to config
    document.getElementById('genesis-config').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetGenesis() {
    document.querySelectorAll('.preset-card').forEach(c => {
        c.classList.remove('border-amber-500', 'bg-gray-700/50');
        c.classList.add('border-gray-700');
    });
    document.getElementById('genesis-config').classList.add('hidden');
    document.getElementById('preset_key').value = '';
    document.getElementById('name').value = '';
}
</script>
@endsection
