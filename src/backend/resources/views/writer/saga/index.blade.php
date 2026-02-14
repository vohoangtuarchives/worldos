@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">Khám phá Saga</h2>
            <p class="mt-2 text-sm text-gray-400">Xem lại các mô phỏng trước đây và phân tích lịch sử phát sinh của các thế giới của bạn.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('writer.dashboard') }}" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Mô phỏng Mới
            </a>
        </div>
    </div>

    <div class="bg-gray-800 shadow ring-1 ring-gray-700 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-700">
            <thead>
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Tên Saga</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Trạng thái</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Thế giới</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Di sản</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Ngày tạo</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Xem</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700 bg-gray-800">
                @foreach($sagas as $saga)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-white sm:pl-6">
                            {{ $saga->name }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $saga->isComplete() ? 'bg-green-400/10 text-green-400 ring-green-400/20' : ($saga->isRunning() ? 'bg-blue-400/10 text-blue-400 ring-blue-400/20' : 'bg-gray-400/10 text-gray-400 ring-gray-400/20') }}">
                                {{ ucfirst($saga->status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                            {{ $saga->current_world_index }} / {{ $saga->world_count }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                            {{ $saga->carry_legacy ? 'Yes' : 'No' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                            {{ $saga->created_at->format('M d, Y') }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a href="{{ route('writer.sagas.show', $saga) }}" class="text-indigo-400 hover:text-indigo-300">View<span class="sr-only">, {{ $saga->name }}</span></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-700">
            {{ $sagas->links() }}
        </div>
    </div>
</div>
@endsection
