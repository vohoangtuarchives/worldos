@extends('layouts.writer')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                Narrative Material Engine
            </h2>
            <p class="mt-1 text-sm text-gray-400">
                Generate story premises by combining Power Systems, Social Structures, and Twists.
            </p>
        </div>
    </div>

    <!-- Generator Section -->
    <div class="bg-gray-800 shadow sm:rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-medium leading-6 text-white mb-4">Material Generator</h3>
        <form action="{{ route('writer.materials.generate') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-4">
                <!-- Power System -->
                <div>
                    <label for="power_system" class="block text-sm font-medium text-gray-400">Power System</label>
                    <select id="power_system" name="power_system" class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        <option value="">Random</option>
                        @foreach($seeds->get('power_system', []) as $seed)
                            <option value="{{ $seed->id }}">{{ $seed->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Social Structure -->
                <div>
                    <label for="social_structure" class="block text-sm font-medium text-gray-400">Social Structure</label>
                    <select id="social_structure" name="social_structure" class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        <option value="">Random (Compatible)</option>
                         @foreach($seeds->get('social_structure', []) as $seed)
                            <option value="{{ $seed->id }}">{{ $seed->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Twist -->
                <div>
                    <label for="twist" class="block text-sm font-medium text-gray-400">Plot Twist</label>
                    <select id="twist" name="twist" class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        <option value="">Random</option>
                         @foreach($seeds->get('twist', []) as $seed)
                            <option value="{{ $seed->id }}">{{ $seed->name }}</option>
                        @endforeach
                    </select>
                </div>

                 <!-- Hidden Truth -->
                <div>
                    <label for="hidden_truth" class="block text-sm font-medium text-gray-400">Hidden Truth</label>
                    <select id="hidden_truth" name="hidden_truth" class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        <option value="">Random</option>
                         @foreach($seeds->get('hidden_truth', []) as $seed)
                            <option value="{{ $seed->id }}">{{ $seed->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-amber-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                    Generate Premise
                </button>
            </div>
        </form>
    </div>

    <!-- Result Display -->
    @if(session('generated_premise'))
        @php $premise = session('generated_premise'); @endphp
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl border border-amber-500/30 p-8 shadow-2xl relative overflow-hidden">
             <!-- Background decoration -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-amber-500/10 blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-300">
                            {{ $premise->title }}
                        </h3>
                        <p class="mt-2 text-lg text-gray-300 italic">
                            {!! nl2br(e($premise->summary)) !!}
                        </p>
                    </div>
                <div class="flex space-x-3">
                     <form action="{{ route('writer.materials.save', $premise->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-700 px-4 py-2 text-sm font-medium text-amber-500 shadow-sm hover:bg-gray-600 ring-1 ring-amber-500/50">
                            <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.699-3.181a1 1 0 011.827.83l-1.467 6.347 4.143 3.902a1 1 0 01-1.349 1.48L14.7 13.916 11.83 17.653a1 1 0 01-1.636 0L7.3 13.916 3.12 15.28a1 1 0 01-1.349-1.48l4.143-3.902-1.467-6.347a1 1 0 011.827-.83l1.699 3.181L10 3.323V3a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Save
                        </button>
                    </form>

                    <form action="{{ route('writer.genesis.incarnate', $premise->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 ring-1 ring-indigo-500 transition-all duration-200 shadow-indigo-500/30">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Incarnate
                        </button>
                    </form>
                </div>

                <!-- Escalation Tiers -->
                <div class="mt-8">
                    <h4 class="text-sm font-semibold text-amber-500 uppercase tracking-wider mb-3">Escalation Ladder</h4>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach($premise->power_escalation as $tier => $name)
                            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700 text-center">
                                <span class="block text-xs text-gray-500">{{ $tier }}</span>
                                <span class="block text-sm font-medium text-white">{{ $name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Premises -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-lg font-medium leading-6 text-white">Recent Generated Premises</h3>
            <div class="bg-gray-800 shadow sm:rounded-lg border border-gray-700 overflow-hidden">
                <ul class="divide-y divide-gray-700">
                    @forelse($premises as $p)
                        <li class="p-4 hover:bg-gray-700/50 transition duration-150">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-sm font-medium text-indigo-400">{{ $p->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $p->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <form action="{{ route('writer.genesis.incarnate', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs bg-indigo-900/50 text-indigo-300 px-2 py-1 rounded border border-indigo-700 hover:bg-indigo-800">
                                            Incarnate
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-400 line-clamp-2">{{ $p->summary }}</p>
                             @if($p->is_favorite)
                                <span class="inline-flex mt-2 items-center rounded-full bg-yellow-900/30 px-2 py-0.5 text-xs font-medium text-yellow-500 border border-yellow-700">Saved</span>
                            @endif
                        </li>
                    @empty
                        <li class="p-4 text-sm text-gray-500 text-center">No premises yet. Generate one!</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Seeds Library -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium leading-6 text-white">Available Seeds</h3>
            <div class="bg-gray-800 shadow sm:rounded-lg border border-gray-700 p-4">
                <div class="space-y-4">
                    @foreach($seeds as $type => $group)
                        <div>
                            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ str_replace('_', ' ', ucfirst($type)) }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($group as $seed)
                                    <span class="inline-flex items-center rounded bg-gray-700 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-600 pointer-events-none">
                                        {{ $seed->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
