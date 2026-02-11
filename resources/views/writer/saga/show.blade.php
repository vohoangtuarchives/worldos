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

    <!-- Timeline / Worlds -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-700">
            <h3 class="text-base font-semibold leading-6 text-white">Dòng thời gian Saga</h3>
            <p class="mt-1 text-sm text-gray-400">Trình tự thời gian của các thế giới trong mô phỏng này.</p>
        </div>
        <ul role="list" class="divide-y divide-gray-700">
            @foreach($worlds as $sagaWorld)
                <li class="group hover:bg-gray-700/50 transition-colors">
                    <a href="{{ route('writer.sagas.worlds.show', [$saga, $sagaWorld->sequence]) }}" class="block px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full {{ $sagaWorld->hasCollapsed() ? 'bg-red-900/50 border border-red-700' : 'bg-indigo-900/50 border border-indigo-700' }}">
                                    <span class="text-sm font-medium {{ $sagaWorld->hasCollapsed() ? 'text-red-400' : 'text-indigo-400' }}">
                                        {{ $sagaWorld->sequence + 1 }}
                                    </span>
                                </span>
                                <div class="ml-4">
                                    <div class="font-medium text-white">{{ $sagaWorld->world->name }}</div>
                                    <div class="text-sm text-gray-500 capitalize">{{ $sagaWorld->status }}</div>
                                </div>
                            </div>
                            
                            <!-- Legacy Info -->
                            @if($sagaWorld->archetype_legacy)
                                <div class="hidden md:flex ml-8 space-x-2">
                                    @php $dominants = collect($sagaWorld->archetype_legacy)->where('type', 'dominance')->keys()->take(3); @endphp
                                    @foreach($dominants as $key)
                                        <span class="inline-flex items-center rounded-full bg-gray-700 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-600">
                                            {{ str_replace('_', ' ', $key) }}
                                        </span>
                                    @endforeach
                                    @if(count($sagaWorld->archetype_legacy) > 3)
                                        <span class="text-xs text-gray-500">+{{ count($sagaWorld->archetype_legacy) - 3 }} thêm</span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center text-gray-400">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Collapse Context -->
                        @if($sagaWorld->hasCollapsed())
                            <div class="mt-4 ml-14 rounded-md bg-red-900/20 p-3 border border-red-900/50">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-400">Sụp đổ Lịch sử</h3>
                                        <div class="mt-1 text-sm text-red-300/80">
                                            Lực lượng Chủ đạo: <span class="font-semibold">{{ ucwords($sagaWorld->collapse_context['dominant_archetype'] ?? 'Không xác định') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
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
