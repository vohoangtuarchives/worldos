@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                Bắt đầu Mô phỏng
            </h2>
            <p class="mt-1 text-sm text-gray-400">
                Khởi tạo một Saga mới để khám phá các nhánh kể chuyện và lịch sử phát sinh.
            </p>
        </div>
    </div>

    <!-- Genesis Call to Action -->
    <div class="relative isolate overflow-hidden bg-gray-900 px-6 py-24 shadow-2xl sm:rounded-3xl sm:px-24 xl:py-32 border border-gray-800">
        <h2 class="mx-auto max-w-2xl text-center text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Khai Thiên Tịch Địa
            <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-600">Sáng tạo Thế giới Mới</span>
        </h2>
        <p class="mx-auto mt-2 max-w-xl text-center text-lg leading-8 text-gray-300">
            Khởi tạo một vũ trụ mới với hệ thống vật lý, cấp độ công nghệ và cấu trúc xã hội tùy chỉnh. Sử dụng Material Engine để mô phỏng sự thăng trầm của các nền văn minh.
        </p>
        <div class="mt-10 flex justify-center gap-x-6">
            <a href="{{ route('writer.genesis') }}" class="rounded-md bg-gradient-to-r from-amber-500 to-orange-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-amber-400 hover:to-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 transition-all hover:scale-105">
                Khởi động Genesis System <span aria-hidden="true">→</span>
            </a>
            <a href="{{ route('writer.terminology') }}" class="text-sm font-semibold leading-6 text-white hover:text-amber-400 transition-colors">
                Tìm hiểu về Material Engine <span aria-hidden="true">→</span>
            </a>
        </div>
        <svg viewBox="0 0 1024 1024" class="absolute left-1/2 top-1/2 -z-10 h-[64rem] w-[64rem] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]" aria-hidden="true">
            <circle cx="512" cy="512" r="512" fill="url(#gradient)" fill-opacity="0.15" />
            <defs>
                <radialGradient id="gradient">
                    <stop stop-color="#CA8A04" /> <!-- Amber-600 -->
                    <stop offset="1" stop-color="#EA580C" /> <!-- Orange-600 -->
                </radialGradient>
            </defs>
        </svg>
    </div>

    <!-- Recent Sagas -->
    @if($recent_sagas->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-medium leading-6 text-gray-200 mb-4">Mô phỏng Gần đây</h3>
            <div class="overflow-hidden bg-gray-800 shadow sm:rounded-md border border-gray-700">
                <ul role="list" class="divide-y divide-gray-700">
                    @foreach($recent_sagas as $saga)
                        <li>
                            <a href="{{ route('writer.sagas.show', $saga) }}" class="block hover:bg-gray-700/50">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <p class="truncate text-sm font-medium text-indigo-400">{{ $saga->name }}</p>
                                        <div class="ml-2 flex flex-shrink-0">
                                            <p class="inline-flex rounded-full bg-green-900/50 px-2 text-xs font-semibold leading-5 text-green-300 border border-green-800">
                                                {{ ucfirst($saga->status) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <div class="sm:flex">
                                            <p class="flex items-center text-sm text-gray-400">
                                                <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                                </svg>
                                                Started {{ $saga->created_at->diffForHumans() }}
                                            </p>
                                            <p class="mt-2 flex items-center text-sm text-gray-400 sm:mt-0 sm:ml-6">
                                                <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $saga->current_world_index }} / {{ $saga->world_count }} Worlds
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
