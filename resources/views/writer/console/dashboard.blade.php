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

    <!-- Create Saga Form -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('writer.sagas.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="action_type" value="seed_archetype">

                <!-- Saga Name -->
                <div>
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-200">Tên Saga</label>
                    <div class="mt-2">
                        <input type="text" name="name" id="name" 
                               class="block w-full rounded-md border-0 bg-gray-900 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 placeholder-gray-500" 
                               placeholder="ví dụ: Sự sụp đổ của Hyperion" required>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Một tên mô tả cho chuỗi thế giới này.</p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <!-- World Count -->
                    <div class="sm:col-span-3">
                        <label for="world_count" class="block text-sm font-medium leading-6 text-gray-200">Số lượng Thế giới</label>
                        <div class="mt-2">
                            <input type="number" name="world_count" id="world_count" value="5" min="1" max="20"
                                   class="block w-full rounded-md border-0 bg-gray-900 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Bao nhiêu thế giới tuần tự để tạo ra.</p>
                    </div>

                    <!-- Legacy Toggle -->
                    <div class="sm:col-span-3">
                        <label for="carry_legacy" class="block text-sm font-medium leading-6 text-gray-200">Chuyển giao Di sản</label>
                        <div class="mt-2 flex items-center">
                            <select name="carry_legacy" id="carry_legacy" class="block w-full rounded-md border-0 bg-gray-900 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="1">Bật (Lịch sử quan trọng)</option>
                                <option value="0">Tắt (Khởi đầu mới mỗi lần)</option>
                            </select>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Chuyển giao thiên hướng nguyên mẫu sang thế giới tiếp theo?</p>
                    </div>

                    <!-- Genre Selection -->
                    <div class="sm:col-span-3">
                        <label for="genre" class="block text-sm font-medium leading-6 text-gray-200">Thể loại (World Physics)</label>
                        <div class="mt-2 flex items-center">
                            <select name="genre" id="genre" class="block w-full rounded-md border-0 bg-gray-900 py-1.5 text-white shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="historical">Historical (Default)</option>
                                <option value="xianxia">Xianxia (Cultivation)</option>
                                <option value="wuxia">Wuxia (Martial Arts)</option>
                                <option value="system">System (LitRPG)</option>
                                <option value="magical_academy">Magical Academy</option>
                            </select>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Quy tắc vật lý và hệ thống sức mạnh chủ đạo.</p>
                    </div>
                </div>

                <!-- Archetype Focus Selection -->
                <div class="border-t border-gray-700 pt-6">
                    <fieldset>
                        <legend class="text-base font-semibold leading-6 text-white">Hạt giống Nguyên mẫu Ban đầu</legend>
                        <p class="mt-1 text-sm text-gray-500">Chọn các chủ đề chủ đạo để định hướng thế giới ban đầu.</p>
                        
                        <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 md:grid-cols-4 gap-x-4">
                            @foreach($themes as $domain => $domainThemes)
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium text-indigo-400 uppercase tracking-wider">{{ $domain }}</h3>
                                    @foreach($domainThemes as $theme)
                                        <div class="relative flex items-start">
                                            <div class="flex h-6 items-center">
                                                <input id="theme_{{ $theme['key'] }}" name="archetype_focus[]" value="{{ $theme['key'] }}" type="checkbox" 
                                                       class="h-4 w-4 rounded border-gray-600 bg-gray-900 text-indigo-600 focus:ring-indigo-600 focus:ring-offset-gray-900">
                                            </div>
                                            <div class="ml-3 text-sm leading-6">
                                                <label for="theme_{{ $theme['key'] }}" class="font-medium text-gray-200">{{ $theme['name'] }}</label>
                                                <p class="text-gray-500 text-xs">{{ Str::limit($theme['description'], 50) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                </div>

                <div class="border-t border-gray-700 pt-6 flex items-center justify-end gap-x-6">
                    <button type="button" class="text-sm font-semibold leading-6 text-white">Hủy</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Khởi tạo Mô phỏng
                    </button>
                </div>
            </form>
        </div>
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
