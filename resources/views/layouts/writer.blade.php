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
                <!-- Dashboard -->
                <a href="{{ route('writer.dashboard') }}" 
                   class="{{ request()->routeIs('writer.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.dashboard') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                    </svg>
                    Bảng điều khiển
                </a>

                <a href="{{ route('writer.genesis') }}" 
                   class="{{ request()->routeIs('writer.genesis') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.genesis') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l-7.5 7.5m0 0l-1.5-1.5m1.5 1.5l1.5 1.5m0-7.5L12 3m0 0l1.5 1.5M12 3v18m4.5-13.5L12 3m0 0l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Khởi tạo Thế giới (Genesis)
                </a>

                <!-- Saga Exploration -->
                <div class="pt-4 pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Khám phá</span>
                </div>
                
                <a href="{{ route('writer.sagas.index') }}" 
                   class="{{ request()->routeIs('writer.sagas.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.sagas.*') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    Saga của tôi
                </a>

                <!-- Material Tools -->
                <div class="pt-4 pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Công cụ Vật liệu</span>
                </div>
                
                <a href="#" onclick="alert('Vui lòng chọn một thế giới cụ thể từ trong Saga để quản lý vật liệu.')" 
                   class="text-gray-400 hover:bg-gray-700 hover:text-white group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-500 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Quản lý Vật liệu
                </a>
                
                <a href="#" onclick="alert('Chọn một world từ Saga để xem timeline')" 
                   class="text-gray-400 hover:bg-gray-700 hover:text-white group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-500 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Timeline Sự kiện
                </a>

                <!-- Help -->
                <div class="pt-4 pb-2">
                    <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trợ giúp</span>
                </div>
                
                <a href="{{ route('writer.terminology') }}" 
                   class="{{ request()->routeIs('writer.terminology') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('writer.terminology') ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                    Thuật ngữ
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
</body>
</html>
