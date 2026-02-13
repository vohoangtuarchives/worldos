@extends('layouts.writer')

@section('content')
<div class="space-y-6">
    {{-- World Header --}}
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                🌍 {{ $world->name }}
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <span class="mt-2 flex items-center text-sm text-gray-400">
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                        {{ $world->health_status === 'STABLE' ? 'bg-green-400/10 text-green-400 ring-green-400/20' : 
                           ($world->health_status === 'CRITICAL' ? 'bg-red-400/10 text-red-400 ring-red-400/20' : 
                           'bg-yellow-400/10 text-yellow-400 ring-yellow-400/20') }}">
                        {{ $world->health_status }}
                    </span>
                </span>
                <span class="mt-2 flex items-center text-sm text-gray-400">
                    🎭 {{ ucfirst($world->genre) }}
                </span>
                <span class="mt-2 flex items-center text-sm text-gray-400">
                    📦 {{ $world->preset }}
                </span>
                <span class="mt-2 flex items-center text-sm text-gray-400 font-mono">
                    Epoch {{ $world->current_epoch }} · Tick {{ $world->tick }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-700">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            @php
                $tabs = [
                    'overview'  => ['label' => '🌍 Tổng quan',   'icon' => null],
                    'cosmic'    => ['label' => '🔮 Cosmic',      'icon' => null],
                    'materials' => ['label' => '⚗️ Vật liệu',    'icon' => null],
                    'story'     => ['label' => '📖 Câu chuyện',  'icon' => null],
                    'social'    => ['label' => '👥 Xã hội',      'icon' => null],
                    'heroes'    => ['label' => '🇻🇳 Anh Hùng',    'icon' => null],
                    'controls'  => ['label' => '🎮 Điều khiển',  'icon' => null],
                ];
            @endphp

            @foreach($tabs as $tabKey => $tabMeta)
                <a href="{{ route('writer.worlds.hub', ['worldId' => $world->id, 'tab' => $tabKey]) }}"
                   class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors
                       {{ $activeTab === $tabKey 
                          ? 'border-indigo-400 text-indigo-400' 
                          : 'border-transparent text-gray-400 hover:border-gray-500 hover:text-gray-300' }}">
                    {{ $tabMeta['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-md bg-green-900/50 border border-green-700 p-4">
            <div class="flex">
                <div class="flex-shrink-0">✅</div>
                <div class="ml-3 text-sm text-green-300">{{ session('success') }}</div>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-md bg-red-900/50 border border-red-700 p-4">
            <div class="flex">
                <div class="flex-shrink-0">❌</div>
                <div class="ml-3 text-sm text-red-300">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Tab Content --}}
    @include('writer.world-hub._' . $activeTab)
</div>
@endsection
