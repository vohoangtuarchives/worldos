@if(!$isVietnamese)
    <div class="text-center py-12">
        <div class="mx-auto h-24 w-24 text-gray-600">
            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h3 class="mt-2 text-sm font-semibold text-white">Chức năng không khả dụng</h3>
        <p class="mt-1 text-sm text-gray-400">Dòng thời gian Anh Hùng chỉ dành cho thế giới có nguồn gốc Thần Thoại Việt Nam.</p>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Timeline: Active Heroes -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-700 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="text-2xl">🇻🇳</span> Anh Hùng Đương Đại (Kỷ Nguyên {{ $currentEra }})
                </h3>
                <span class="px-3 py-1 bg-red-900/30 text-red-400 rounded-full text-xs font-mono border border-red-800">
                    Active: {{ $activeHeroes->count() }}
                </span>
            </div>

            @if($activeHeroes->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-700 p-12 text-center">
                    <p class="text-gray-400">Không có anh hùng nào nổi bật trong kỷ nguyên này.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($activeHeroes as $hero)
                        <div class="group relative overflow-hidden rounded-xl border border-gray-700 bg-gray-800/50 p-6 transition-all hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10">
                            <!-- Background Decoration -->
                            <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-gradient-to-br from-amber-500/5 to-transparent blur-2xl transition-all group-hover:from-amber-500/10"></div>

                            <div class="relative z-10 flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-xl font-bold text-white">{{ $hero->name }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-700 text-gray-300 border border-gray-600">
                                            {{ $hero->period }}
                                        </span>
                                        @if($hero->canTriggerBifurcation())
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-900/50 text-yellow-500 border border-yellow-700 animate-pulse">
                                                ⚡ BIFURCATION READY
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-400 max-w-xl">{{ Str::limit($hero->achievements ?? 'Người hùng của dân tộc...', 120) }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 uppercase tracking-wider">Impact</div>
                                    <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">
                                        {{ number_format($hero->impact_score, 2) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Dimensions Grid -->
                            <div class="mt-6">
                                <div class="text-xs text-gray-500 mb-2 uppercase tracking-wider font-semibold">Top Dimensions</div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($hero->topDimensions as $dim => $val)
                                        <div class="rounded-lg bg-gray-900/50 p-2 border border-gray-700/50 flex flex-col items-center">
                                            <span class="text-[10px] text-gray-400 capitalize">{{ $dim }}</span>
                                            <div class="w-full bg-gray-700 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                                <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $val * 100 }}%"></div>
                                            </div>
                                            <span class="text-xs font-mono text-indigo-300 mt-1">{{ number_format($val, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- View Details Button -->
                            <div class="mt-4 pt-4 border-t border-gray-700/50 flex justify-end">
                                <button class="text-xs font-medium text-amber-500 hover:text-amber-400 transition-colors flex items-center gap-1">
                                    Xem chi tiết
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sidebar: History & Stats -->
        <div class="space-y-8">
            <!-- Stats Panel -->
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <h4 class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">
                    Tổng Quan Văn Minh
                </h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-400">Thời Đại:</span>
                        <span class="text-sm font-mono text-white">Kỷ {{ $currentEra }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-400">Nguồn Gốc:</span>
                        <span class="text-sm font-medium text-red-400 flex items-center gap-1">
                            <span class="text-lg">🇻🇳</span> Thần Thoại VN
                        </span>
                    </div>
                    <!-- Placeholder for Boosts Display -->
                    <div class="pt-4 border-t border-gray-700 mt-2">
                        <span class="text-xs text-gray-500 block mb-2">Civilization Boosts (Current Era)</span>
                        <!-- This would ideally pull from SagaRunner logic or stored boosts -->
                        <div class="text-center text-xs text-gray-600 italic">Boosts applied by active heroes</div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Realm Contacts (NEW) -->
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <h4 class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-700 pb-2 flex justify-between items-center">
                    <span>Ngoại Bang & Ảnh Hưởng</span>
                    <span class="text-xs text-gray-500 font-normal">Active: {{ $realmContacts->count() }}</span>
                </h4>
                
                @if($realmContacts->isEmpty())
                     <p class="text-xs text-center text-gray-500 italic">Không có áp lực ngoại bang trong kỷ nguyên này.</p>
                @else
                    <!-- Active Contacts List -->
                    <ul class="space-y-3 mb-6">
                        @foreach($realmContacts as $contact)
                            <li class="group">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ match($contact->realm_name) { 'Northern Empire' => '🇨🇳', 'Western Empire' => '🇫🇷', 'Superpower' => '🇺🇸', 'Champa Kingdom' => '🏛️', default => '🏳️' } }}</span>
                                            <span class="text-sm font-bold text-gray-300 group-hover:text-red-400 transition-colors">{{ $contact->period_name }}</span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 uppercase tracking-wide ml-7">{{ $contact->influence_type }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-mono font-bold text-red-400">{{ $contact->intensity * 100 }}%</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-700 h-1 rounded-full mt-2 ml-7 overflow-hidden w-[calc(100%-1.75rem)]">
                                    <div class="bg-red-500 h-full rounded-full" style="width: {{ $contact->intensity * 100 }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Modifiers -->
                    @if(!empty($realmModifiers))
                    <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50">
                        <span class="text-[10px] text-gray-500 uppercase block mb-2 font-bold">Tác Động Mô Phỏng</span>
                        <div class="space-y-1">
                            @foreach($realmModifiers as $key => $val)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="font-mono {{ $val > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $val > 0 ? '+' : '' }}{{ number_format($val, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Past Heroes -->
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <h4 class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">
                    Huyền Thoại Quá Khứ
                </h4>
                @if($pastHeroes->isEmpty())
                    <p class="text-xs text-center text-gray-500 italic">Chưa có anh hùng nào trong quá khứ.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($pastHeroes as $historyHero)
                            <li class="flex items-center justify-between group">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-400 group-hover:bg-amber-900 group-hover:text-amber-500 transition-colors">
                                        {{ substr($historyHero->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-300 group-hover:text-amber-400 transition-colors">{{ $historyHero->name }}</p>
                                        <p class="text-[10px] text-gray-500">Kỷ {{ $historyHero->era }}</p>
                                    </div>
                                </div>
                                <span class="text-xs font-mono text-gray-600">{{ number_format($historyHero->impact_score, 1) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endif
