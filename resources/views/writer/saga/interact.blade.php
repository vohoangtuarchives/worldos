@extends('layouts.writer')

@section('content')
<div class="container mx-auto px-4 py-8 text-slate-200">
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-baseline gap-4">
                <h1 class="text-3xl font-bold text-amber-500">{{ $world->name }}</h1>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] border border-slate-700 px-2 py-1 rounded">
                    {{ strtoupper($genre ?? 'Historical') }} Chronicle
                </span>
            </div>
            <p class="text-xl text-slate-400">Epoch {{ $epoch }}</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-slate-800 rounded border border-slate-700">Inequality: {{ number_format($choices[0]['state']['inequality'] ?? 0, 2) }}</span>
            <span class="px-3 py-1 bg-slate-800 rounded border border-slate-700">Trauma: {{ number_format($choices[0]['state']['trauma'] ?? 0, 2) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Column: Narrative & Choices -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Narrative Log -->
            <div class="bg-slate-900 rounded-lg p-6 border border-slate-800 shadow-lg">
                <h2 class="text-lg font-semibold text-slate-300 mb-4 border-b border-slate-700 pb-2">Chronicle</h2>
                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                    @forelse($narrative as $event)
                        <div class="p-4 rounded bg-slate-800/50 border-l-4 border-amber-600">
                            <span class="text-xs font-mono text-amber-500 mb-1 block">Epoch {{ $event->epoch }}</span>
                            <p class="text-slate-300">{{ $event->content }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500 italic">History has yet to be written...</p>
                    @endforelse
                </div>
            </div>

            <!-- Decisions Area -->
            <div class="bg-slate-900 rounded-lg p-6 border border-amber-900/30 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-600 to-purple-600"></div>
                <h2 class="text-xl font-bold text-amber-100 mb-6">Decisions Required</h2>

                @if(empty($choices))
                    <div class="text-center py-8">
                        <p class="text-slate-400 mb-6">The world turns without intervention this epoch.</p>
                        <form action="{{ route('reader.advance', $world->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded font-semibold transition-colors">
                                Let Time Pass (Next Epoch)
                            </button>
                        </form>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($choices as $choice)
                            <div class="bg-slate-800/80 p-6 rounded border border-slate-700">
                                <span class="text-xs uppercase tracking-wider text-amber-500 font-bold mb-2 block">{{ $choice['category'] }}</span>
                                <h3 class="text-lg font-bold text-white mb-2">{{ $choice['question'] }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    @foreach($choice['options'] as $option)
                                        <form action="{{ route('reader.choice', $world->id) }}" method="POST" class="h-full">
                                            @csrf
                                            <input type="hidden" name="choice_id" value="{{ $choice['id'] }}">
                                            <input type="hidden" name="option_id" value="{{ $option['id'] }}">
                                            
                                            <button type="submit" class="w-full h-full p-4 text-left rounded bg-slate-700 hover:bg-slate-600 border border-slate-600 hover:border-amber-500 transition-all group">
                                                <span class="block font-bold text-amber-100 group-hover:text-amber-400 mb-1">{{ $option['text'] }}</span>
                                                <span class="block text-sm text-slate-400 group-hover:text-slate-300">{{ $option['description'] ?? 'No detail available.' }}</span>
                                                
                                                @if(!empty($option['delta']))
                                                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                                        @foreach($option['delta'] as $key => $val)
                                                            <span class="{{ $val > 0 ? 'text-green-400' : 'text-red-400' }}">
                                                                {{ $key }}: {{ $val > 0 ? '+' : '' }}{{ $val }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Sidebar: Dashboard -->
        <div class="space-y-6">
             <!-- Publish Actions -->
             <div class="bg-slate-900 rounded-lg p-6 border border-amber-500/50 shadow-[0_0_15px_rgba(245,158,11,0.1)]">
                <h3 class="text-sm font-bold text-amber-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.246 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Công cụ Xuất bản
                </h3>
                <p class="text-xs text-slate-400 mb-4">Gom bản thảo (Chronicles) và đóng gói thành truyện chính thức với phân chương Tiếng Việt.</p>
                
                <form action="{{ route('story.publish', $world->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-white rounded font-bold transition-all shadow-lg flex items-center justify-center gap-2">
                        Xuất bản truyện
                    </button>
                </form>
            </div>

             <!-- Status -->
             <div class="bg-slate-900 rounded-lg p-6 border border-slate-800">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 text-center">World Vitality</h3>
                <p class="text-xs text-slate-500 text-center">Real-time metrics visualization (Coming Soon)</p>
            </div>
        </div>
    </div>
</div>
@endsection
