{{-- STORY TAB --}}
<div class="space-y-6">
    {{-- Story Status --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">📖 Câu chuyện của thế giới</h3>
                <p class="mt-1 text-sm text-gray-400">
                    @if(isset($story) && $story)
                        Story đã được xuất bản • {{ $story->title ?? 'Untitled' }}
                    @else
                        Chưa có story được xuất bản
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                @if(isset($story) && $story)
                    <a href="{{ route('writer.story.show', $story) }}" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        📖 Đọc Truyện
                    </a>
                @elseif(isset($chronicles) && $chronicles->count() > 0)
                    <form action="{{ route('writer.story.publish', $world->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-500">
                            📝 Xuất bản Truyện
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Chronicles --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-base font-semibold text-white">📜 Biên niên sử</h3>
            <span class="text-xs text-gray-400">{{ $chronicles->count() ?? 0 }} sự kiện</span>
        </div>
        <div class="divide-y divide-gray-700">
            @forelse($chronicles as $chronicle)
                <div class="px-5 py-4 hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-20 text-right">
                            <span class="text-sm font-bold font-mono text-indigo-400">E{{ $chronicle->epoch }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-200 whitespace-pre-line leading-relaxed">{{ $chronicle->content }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <p class="text-sm text-gray-500">Chưa có biên niên sử. Chạy mô phỏng để tạo lịch sử cho thế giới này.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
