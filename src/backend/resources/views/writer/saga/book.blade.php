<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $story->title }} - Official Reader</title>
    
    <!-- Fonts: Serif for reading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Merriweather"', 'serif'],
                        display: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        paper: '#fdfbf7',
                        ink: '#2d2d2d',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f3f4f6;
        }
        .prose p {
            margin-bottom: 1.5em;
            text-align: justify;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center py-10">

    <!-- Book Container -->
    <div class="w-full max-w-3xl bg-paper shadow-2xl rounded-sm overflow-hidden min-h-[90vh] flex flex-col relative">
        
        <!-- Cover / Header -->
        <header class="bg-gradient-to-b from-gray-900 to-gray-800 text-white p-12 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')]"></div>
            
            <div class="relative z-10">
                <span class="inline-block py-1 px-3 border border-white/30 rounded-full text-xs tracking-widest uppercase mb-4 text-white/70">
                    {{ $worldState['genre'] ?? 'Tiểu Thuyết' }}
                </span>
                
                <h1 class="font-display text-4xl md:text-5xl font-bold mb-4 leading-tight">
                    {{ $story->title }}
                </h1>
                
                <p class="font-serif italic text-white/80 max-w-xl mx-auto mb-8">
                    {{ optional(json_decode($story->config))->description ?? 'Một biên niên sử về sự trỗi dậy và sụp đổ của các nền văn minh.' }}
                </p>

                <div class="flex flex-wrap justify-center gap-2">
                    @foreach($worldState['tags'] ?? [] as $tag)
                        <span class="px-2 py-1 bg-white/10 rounded text-xs text-white/90">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        </header>

        <!-- Navigation (TOC) -->
        <nav class="bg-gray-100 border-b border-gray-200 px-8 py-4 sticky top-0 z-20 shadow-sm flex justify-between items-center">
            <div class="font-display font-bold text-gray-800 text-lg">Mục Lục</div>
            <div class="text-xs text-gray-500 uppercase tracking-widest">{{ $story->chapters->count() }} Chương</div>
        </nav>

        <!-- Content Area -->
        <main class="flex-1 px-8 md:px-16 py-12">
            
            @forelse($story->chapters as $chapter)
                <article class="mb-24 scroll-mt-24" id="chapter-{{ $chapter->order }}">
                    <div class="flex items-center justify-center mb-8">
                        <span class="h-px w-12 bg-gray-300"></span>
                        <span class="mx-4 text-gray-400 font-serif italic">Chương {{ $chapter->order }}</span>
                        <span class="h-px w-12 bg-gray-300"></span>
                    </div>

                    <h2 class="font-display text-3xl font-bold text-center text-gray-900 mb-10">
                        {{ str_replace("Chương {$chapter->order}: ", '', $chapter->title) }}
                    </h2>

                    <div class="prose prose-lg prose-slate mx-auto font-serif text-ink leading-loose">
                        @foreach(explode("\n\n", $chapter->content) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="text-center py-20 text-gray-500">
                    <p class="mb-4">Chưa có nội dung nào được biên soạn.</p>
                </div>
            @endforelse

            <!-- End Signal -->
            <div class="text-center pt-12 border-t border-gray-100 mt-12 pb-12">
                <span class="text-2xl text-gray-300">❦</span>
                <p class="text-sm text-gray-400 mt-2 tracking-widest uppercase">Hết</p>
            </div>

        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-50 p-6 text-center border-t border-gray-200 text-xs text-gray-400 font-serif">
            Generated by World OS Engine v2.0 &middot; {{ now()->year }}
        </footer>

    </div>

    <!-- Floating Action Button (Back to Admin) -->
    <a href="{{ route('writer.sagas.index') }}" class="fixed bottom-8 right-8 bg-gray-900 text-white p-4 rounded-full shadow-lg hover:bg-black transition-all z-50 group" title="Back to Console">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
        </svg>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity">
            Quay lại Console
        </span>
    </a>

</body>
</html>
