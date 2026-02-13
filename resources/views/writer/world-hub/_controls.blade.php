{{-- CONTROLS TAB --}}
<div class="space-y-6">
    {{-- Simulation Controls --}}
    <div class="bg-gray-800 rounded-lg border border-indigo-500/30">
        <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-base font-semibold text-white">⏯️ Điều khiển Mô phỏng</h3>
            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $world->status === 'active' ? 'bg-green-900/50 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                {{ strtoupper($world->status) }}
            </span>
        </div>
        <div class="p-5">
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('writer.worlds.freeze', $world->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600 transition-colors" title="Freeze">
                        ⏸ Freeze
                    </button>
                </form>
                <form method="POST" action="{{ route('writer.worlds.resume', $world->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600 transition-colors" title="Resume">
                        ▶ Resume
                    </button>
                </form>
                <form method="POST" action="{{ route('writer.worlds.step', $world->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 transition-colors" title="Step 1 Epoch">
                        ⏭ Step
                    </button>
                </form>
                <form method="POST" action="{{ route('writer.worlds.rollback', $world->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md bg-red-800 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                            onclick="return confirm('Rollback 1 epoch?')" title="Rollback">
                        ↩ Rollback
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Power State --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="text-base font-semibold text-white">⚡ Power State</h3>
            </div>
            <div class="p-5 space-y-4">
                @if($powerState)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-400">Stage</dt>
                            <dd class="mt-1 text-lg font-bold text-white">{{ $powerState->current_stage ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Pressure</dt>
                            <dd class="mt-1 text-lg font-bold text-amber-400 font-mono">{{ number_format($currentPressure, 2) }}</dd>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Chưa có Power State.</p>
                @endif
            </div>
        </div>

        {{-- Emergency Intervention --}}
        <div class="bg-gray-800 rounded-lg border border-red-500/30">
            <div class="px-5 py-4 border-b border-gray-700 bg-red-900/20 rounded-t-lg">
                <h3 class="text-base font-semibold text-red-400">🚨 Emergency Intervention</h3>
            </div>
            <div class="p-5 space-y-3">
                <form method="POST" action="{{ route('writer.worlds.emergency', [$world->id, 'entropy-shock']) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="magnitude" value="0.15" min="0.05" max="0.3" step="0.05" class="w-20 rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                    <button type="submit" class="flex-1 rounded-md bg-red-800 px-3 py-2 text-sm font-medium text-white hover:bg-red-700" onclick="return confirm('Inject entropy shock?')">
                        💥 Entropy Shock
                    </button>
                </form>
                <form method="POST" action="{{ route('writer.worlds.emergency', [$world->id, 'reduce-rigidity']) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="reduction" value="0.1" min="0.05" max="0.2" step="0.05" class="w-20 rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                    <button type="submit" class="flex-1 rounded-md bg-yellow-800 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-700" onclick="return confirm('Reduce rigidity?')">
                        🔓 Reduce Rigidity
                    </button>
                </form>
                <form method="POST" action="{{ route('writer.worlds.emergency', [$world->id, 'force-collapse']) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-500" onclick="return confirm('⚠️ FORCE COLLAPSE — Are you sure?')">
                        💀 Force Collapse
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Myths & Scars --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Myths --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="text-base font-semibold text-white">🏛️ Myths ({{ $myths->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-700/50">
                @forelse($myths as $myth)
                    <div class="px-5 py-3">
                        <div class="text-sm font-medium text-gray-200">{{ $myth->name ?? $myth->type }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $myth->description ?? '' }}</div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-500">Chưa có Myth</div>
                @endforelse
            </div>
        </div>

        {{-- Scars --}}
        <div class="bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
                <h3 class="text-base font-semibold text-white">⚡ Scars ({{ $scars->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-700/50">
                @forelse($scars as $scar)
                    <div class="px-5 py-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-200">{{ $scar->location_scope }}</span>
                            <span class="text-xs font-mono text-amber-400">{{ number_format($scar->severity, 2) }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">{{ $scar->constraint_rule }}</div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-500">Chưa có Scar</div>
                @endforelse
            </div>

            {{-- Create Scar Form --}}
            <div class="border-t border-gray-700 p-5">
                <h4 class="text-xs font-semibold text-gray-400 uppercase mb-3">Tạo Scar mới</h4>
                <form method="POST" action="{{ route('writer.worlds.scar', $world->id) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="location_scope" placeholder="Location scope" required class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                    <input type="text" name="constraint_rule" placeholder="Constraint rule" required class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                    <input type="number" name="severity" placeholder="Severity (0-1)" min="0" max="1" step="0.1" value="0.5" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                    <button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                        ⚡ Brand Scar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Event Injection --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-base font-semibold text-white">💉 Event Injection</h3>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('writer.worlds.inject', $world->id) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Event Type</label>
                    <select name="event_type" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                        <option value="divine_intervention">Divine Intervention</option>
                        <option value="natural_disaster">Natural Disaster</option>
                        <option value="technological_breakthrough">Technological Breakthrough</option>
                        <option value="cosmic_anomaly">Cosmic Anomaly</option>
                        <option value="social_revolution">Social Revolution</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Visibility</label>
                    <select name="visibility" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                        <option value="public">Public</option>
                        <option value="hidden">Hidden</option>
                        <option value="mythical">Mythical</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1">Description</label>
                    <textarea name="description" rows="2" required class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm" placeholder="Mô tả sự kiện..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Magnitude (0-1)</label>
                    <input type="number" name="magnitude" value="0.5" min="0" max="1" step="0.1" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Permanence (0-1)</label>
                    <input type="number" name="permanence" value="0.5" min="0" max="1" step="0.1" class="w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        💉 Inject Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Recent Events --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-base font-semibold text-white">📋 Recent Event Ledger</h3>
        </div>
        <div class="divide-y divide-gray-700/50">
            @forelse($recentEvents as $event)
                <div class="px-5 py-3 hover:bg-gray-700/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-200">{{ $event->event_type ?? 'event' }}</span>
                            <p class="text-xs text-gray-400 mt-1">{{ $event->description ?? json_encode($event->payload ?? '') }}</p>
                        </div>
                        <span class="text-xs text-gray-500 flex-shrink-0 ml-3">{{ $event->created_at }}</span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-6 text-center text-sm text-gray-500">Chưa có sự kiện</div>
            @endforelse
        </div>
    </div>
</div>
