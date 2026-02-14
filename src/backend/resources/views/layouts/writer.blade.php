<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'World OS') }} - Writer Console</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-100">
    <div class="min-h-full">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 flex w-64 flex-col bg-gray-800 border-r border-gray-700">
            <div class="flex h-16 shrink-0 items-center px-6 bg-gray-900 border-b border-gray-700">
                <span class="text-xl font-bold tracking-tight text-indigo-400">World OS</span>
                <span class="ml-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Writer</span>
            </div>
            
            <nav class="flex-1 flex flex-col px-4 py-6 space-y-1 overflow-y-auto">
                <!-- Main Navigation -->
                <div class="pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Điều hướng</span>
                </div>

                <a href="{{ route('writer.dashboard') }}"
                   class="{{ request()->routeIs('writer.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.dashboard') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Trang chủ
                </a>

                <a href="{{ route('writer.genesis') }}"
                   class="{{ request()->routeIs('writer.genesis*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.genesis*') ? 'text-amber-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    🌅 Khai Thiên (Genesis)
                </a>

                <a href="{{ route('writer.sagas.index') }}"
                   class="{{ request()->routeIs('writer.sagas.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.sagas.*') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    📚 Saga của tôi
                </a>

                <!-- Active Worlds -->
                @php
                    $sidebarWorlds = \App\Models\World::where('status', '!=', 'archived')
                        ->orderByDesc('updated_at')
                        ->limit(5)
                        ->get();
                @endphp
                @if($sidebarWorlds->count() > 0)
                <div class="pt-5 pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">🌍 Thế giới</span>
                </div>

                @foreach($sidebarWorlds as $sw)
                <a href="{{ route('writer.worlds.hub', $sw->id) }}"
                   class="{{ request()->is('writer/worlds/'.$sw->id.'*') ? 'bg-indigo-900/50 text-white border-l-2 border-indigo-400' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <span class="mr-3 h-5 w-5 flex-shrink-0 flex items-center justify-center text-xs {{ $sw->status === 'active' ? 'text-green-400' : 'text-gray-500' }}">●</span>
                    <span class="truncate">{{ $sw->name }}</span>
                    @if($sw->health_status === 'CRITICAL')
                        <span class="ml-auto flex-shrink-0 text-red-400 text-xs">⚠</span>
                    @endif
                </a>
                @endforeach
                @endif

                <!-- Help -->
                <div class="pt-5 pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trợ giúp</span>
                </div>

                <a href="{{ route('writer.terminology') }}"
                   class="{{ request()->routeIs('writer.terminology') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.terminology') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                    📖 Thuật ngữ
                </a>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="inline-block h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                            WR
                        </span>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-white">Writer Console</p>
                        <p class="text-xs font-medium text-gray-400">v1.0.0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="pl-64 flex flex-col min-h-screen">
            <main class="flex-1 py-8 px-8">
                @if(session('success'))
                    <div class="mb-6 rounded-md bg-green-900/50 p-4 border border-green-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-300">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-md bg-red-900/50 p-4 border border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-300">Action Denied</h3>
                                <div class="mt-2 text-sm text-red-400">
                                    <ul role="list" class="list-disc pl-5 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.saga-status-bar')

    @stack('scripts')
</body>
</html>
