@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <div>
                    <a href="{{ route('writer.sagas.index') }}" class="text-gray-400 hover:text-gray-200">
                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                        </svg>
                        <span class="sr-only">Trang chủ</span>
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    <a href="#" class="ml-4 text-sm font-medium text-gray-200 hover:text-white">{{ $saga->name }}</a>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Saga Header -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    {{ $saga->name }}
                </h2>
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-gray-400">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                        </svg>
                        {{ $saga->current_world_index }} / {{ $saga->world_count }} Thế giới
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-400">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.62.829.799 1.654 1.381 2.274 1.766.311.192.571.337.757.433.093.048.17.085.23.115.03.015.055.027.076.036.01.004.02.008.028.011zM10 11.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd" />
                        </svg>
                        Di sản: {{ $saga->carry_legacy ? 'Kích hoạt' : 'Vô hiệu' }}
                    </div>
                </div>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                @if($saga->isPending())
                    <form action="{{ route('writer.sagas.run', $saga) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Bắt đầu Mô phỏng
                        </button>
                    </form>
                @endif
                <button type="button" class="inline-flex items-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-white/20">
                    Xuất Phân tích
                </button>

                <a href="{{ route('writer.sagas.tree', $saga) }}" class="inline-flex items-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Yggdrasil Tree
                </a>
                
                @if(!$saga->isPending() && $saga->sagaWorlds->count() > 0)
                    <form action="{{ route('writer.story.publish', $saga->sagaWorlds->last()->world) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xuất bản Saga này thành truyện chính thức?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zm2.25 8.5a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5zm0 3a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5z" clip-rule="evenodd" />
                            </svg>
                            Xuất Bản Truyện
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- World Laws / Physics Metadata -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-700">
            <h3 class="text-base font-semibold leading-6 text-white">Cấu trúc Vũ trụ (Laws of the World)</h3>
            <p class="mt-1 text-sm text-gray-400">Các quy tắc vật lý và xã hội chi phối chuỗi thế giới này.</p>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Hệ Sức Mạnh</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        <span class="inline-flex items-center rounded-full bg-purple-400/10 px-2 py-1 text-xs font-medium text-purple-400 ring-1 ring-inset ring-purple-400/20">
                            {{ $saga->metadata['power_system'] ?? 'N/A' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Cấp độ Công nghệ</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        <span class="inline-flex items-center rounded-full bg-blue-400/10 px-2 py-1 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-400/20">
                            {{ $saga->metadata['tech_level'] ?? 'N/A' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Cấu trúc Xã hội</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        <span class="inline-flex items-center rounded-full bg-yellow-400/10 px-2 py-1 text-xs font-medium text-yellow-400 ring-1 ring-inset ring-yellow-400/20">
                            {{ $saga->metadata['social_structure'] ?? 'N/A' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Trần Sức Mạnh</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        <span class="inline-flex items-center rounded-full bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-400/20">
                            {{ $saga->metadata['power_ceiling'] ?? 'N/A' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Môi trường</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        {{ $saga->metadata['environment'] ?? 'N/A' }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Khủng hoảng đầu</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        {{ $saga->metadata['starting_crisis'] ?? 'None' }}
                    </dd>
                </div>
                 <div class="sm:col-span-1">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-widest">Xếp hạng Sức mạnh</dt>
                    <dd class="mt-1 text-sm font-semibold text-white">
                        {{ $saga->metadata['power_ranking'] ?? 'Default' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Timeline / Worlds -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-700 bg-gray-900/50">
            <h3 class="text-base font-semibold leading-6 text-white">Dòng thời gian Saga</h3>
            <p class="mt-1 text-sm text-gray-400">Trình tự thời gian của các thế giới trong mô phỏng này.</p>
        </div>
        
        <div class="p-6">
            <div class="relative pl-8 space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-600 before:to-transparent">
                @foreach($worlds as $sagaWorld)
                    <div class="relative group">
                        <!-- Timeline Dot -->
                        <div class="absolute -left-10 mt-1.5 h-6 w-6 rounded-full border-4 border-gray-900 {{ $sagaWorld->hasCollapsed() ? 'bg-red-500' : 'bg-indigo-500' }} shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>

                        <!-- Card Content -->
                        <a href="{{ route('writer.sagas.worlds.show', [$saga, $sagaWorld->sequence]) }}" class="block p-5 rounded-lg border border-gray-700 bg-gray-800 hover:border-indigo-500 hover:bg-gray-750 transition-all duration-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center rounded px-2 py-0.5 text-xs font-bold uppercase {{ $sagaWorld->hasCollapsed() ? 'bg-red-900/30 text-red-400 border border-red-800' : 'bg-indigo-900/30 text-indigo-400 border border-indigo-800' }}">
                                        World {{ $sagaWorld->sequence + 1 }}
                                    </span>
                                    <h4 class="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors">
                                        {{ $sagaWorld->world->name }}
                                    </h4>
                                </div>
                                <span class="text-xs font-mono uppercase tracking-wider {{ $sagaWorld->status === 'running' ? 'text-green-400 animate-pulse' : 'text-gray-500' }}">
                                    {{ $sagaWorld->status }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-400 mb-4 line-clamp-2">
                                {{ $sagaWorld->collapse_context['summary'] ?? 'Chưa có dữ liệu lịch sử chi tiết cho thế giới này.' }}
                            </div>

                            @if($sagaWorld->hasCollapsed())
                                <div class="mt-3 bg-red-950/20 border border-red-900/30 rounded p-3 text-sm">
                                    <div class="flex items-start gap-2 text-red-300/80">
                                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>
                                            Nguyên nhân sụp đổ: <span class="font-bold text-red-400">{{ ucwords($sagaWorld->collapse_context['dominant_archetype'] ?? 'Unknown Force') }}</span>
                                        </span>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Action Footer -->
                            <div class="mt-4 pt-3 border-t border-gray-700/50 flex items-center justify-between">
                                <span class="text-xs text-gray-500">Epoch: {{ floor(($sagaWorld->world->current_time ?? 0) / 100) }}</span>
                                <span class="text-indigo-400 text-xs font-medium group-hover:underline flex items-center">
                                    Khám phá chi tiết
                                    <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Historian Analysis -->
    @if($analysis)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Summary Stats -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h3 class="text-base font-semibold leading-6 text-white mb-4">Tóm tắt Sử học</h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-4">
                    <div class="border-t border-gray-700 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Tỷ lệ Tin cậy</dt>
                        <dd class="mt-1 text-2xl font-semibold tracking-tight text-white">
                            {{ $analysis['summary']['total_worlds'] > 0 ? number_format(($analysis['summary']['survived'] / $analysis['summary']['total_worlds']) * 100, 1) : 0 }}%
                        </dd>
                    </div>
                    <div class="border-t border-gray-700 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Tổng số Sụp đổ</dt>
                        <dd class="mt-1 text-2xl font-semibold tracking-tight text-red-400">{{ $analysis['summary']['collapsed'] }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Key Patterns -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h3 class="text-base font-semibold leading-6 text-white mb-4">Mô hình được Phát hiện</h3>
                <ul class="space-y-3">
                    @forelse($analysis['collapse_analysis']['patterns'] ?? [] as $pattern)
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-1.5 w-1.5 rounded-full bg-indigo-500 mt-2"></span>
                            <span class="ml-2 text-sm text-gray-300">{{ $pattern }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 italic">Chưa phát hiện mô hình đáng kể nào.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
