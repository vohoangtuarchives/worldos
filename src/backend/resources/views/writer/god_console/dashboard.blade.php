@extends('layouts.writer')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
                God Console: {{ $world->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-400">
                Current Phase: <span class="font-mono text-amber-400">{{ ucfirst($world->state->current_phase ?? 'Unknown') }}</span> | Tick: {{ $world->current_tick }}
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('writer.materials.library') }}" class="ml-3 inline-flex items-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-white/20">
                Back to Library
            </a>
        </div>
    </div>

    <!-- State Vector Display -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php 
            $vector = $world->state->state_vector ?? [];
            $metrics = [
                'Coherence' => ['val' => $vector['coherence'] ?? 0, 'color' => 'blue'],
                'Entropy' => ['val' => $vector['entropy'] ?? 0, 'color' => 'red'],
                'Belief Mass' => ['val' => $vector['belief_mass'] ?? 0, 'color' => 'yellow'],
                'Resource Flow' => ['val' => $vector['resource_flow'] ?? 0, 'color' => 'green'],
                'Stability' => ['val' => $vector['stability'] ?? 0, 'color' => 'indigo'],
                'Innovation' => ['val' => $vector['innovation_rate'] ?? 0, 'color' => 'purple'],
            ];
        @endphp

        @foreach($metrics as $label => $data)
            <div class="bg-gray-800 overflow-hidden rounded-lg border border-gray-700">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Icon placeholder -->
                            <div class="h-6 w-6 rounded-full bg-{{ $data['color'] }}-500/20 border border-{{ $data['color'] }}-500"></div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-400">{{ $label }}</dt>
                                <dd>
                                    <div class="text-lg font-medium text-white">{{ number_format($data['val'], 2) }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-700/50 px-5 py-3">
                    <div class="w-full bg-gray-600 rounded-full h-1.5">
                        <div class="bg-{{ $data['color'] }}-500 h-1.5 rounded-full" style="width: {{ $data['val'] * 100 }}%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Intervention Panel -->
    <div class="bg-gray-800 shadow sm:rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-white">Divine Interventions</h3>
            <div class="mt-2 text-sm text-gray-300">
                Inject energy directly into the State Vector to influence the world's trajectory.
            </div>
            
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <form action="{{ route('writer.god_console.intervene', $world->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="inject_belief">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 transition-colors">
                        Inject Belief (+0.1)
                    </button>
                </form>

                <form action="{{ route('writer.god_console.intervene', $world->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="smite">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                        Smite (Entropy++)
                    </button>
                </form>

                <form action="{{ route('writer.god_console.intervene', $world->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="stabilize">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Stabilize (+0.2)
                    </button>
                </form>

                <form action="{{ route('writer.god_console.intervene', $world->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="accelerate">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                        Accelerate Progress
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
