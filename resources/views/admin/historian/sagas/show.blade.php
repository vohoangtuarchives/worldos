@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">📜 Saga Analysis: {{ $saga->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.historian.sagas.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
</div>

@if(!$analysis)
    <div class="alert alert-warning">
        This saga is not yet complete or analysis could not be generated.
    </div>
@else

<div class="row mb-4">
    <!-- Summary Stats -->
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Worlds</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ $analysis['summary']['total_worlds'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Survived</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ $analysis['summary']['survived'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Collapsed</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ $analysis['summary']['collapsed'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Collapse Rate</div>
            <div class="card-body">
                <h5 class="card-title display-4">{{ number_format($analysis['summary']['collapse_rate'] * 100, 1) }}%</h5>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Timeline -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                ⏳ World Timeline
            </div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Seq</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Outcome</th>
                        <th>Legacy Passed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis['timeline'] as $event)
                    <tr>
                        <td>{{ $event['sequence'] + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($event['timestamp'])->format('H:i:s') }}</td>
                        <td>
                            <span class="badge bg-{{ $event['collapsed'] ? 'danger' : 'success' }}">
                                {{ $event['status'] }}
                            </span>
                        </td>
                        <td>{{ $event['collapsed'] ? 'Collapse' : 'Survival' }}</td>
                        <td>
                            <small class="text-muted">
                                {{ count($event['archetype_legacy'] ?? []) }} archetypes
                            </small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Archetype Evolution -->
        <div class="card mb-4">
            <div class="card-header">
                🧬 Archetype Evolution
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Archetype</th>
                                @foreach($analysis['timeline'] as $event)
                                    <th class="text-center">{{ $event['sequence'] + 1 }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analysis['archetype_evolution'] as $key => $evolution)
                            <tr>
                                <td><strong>{{ $key }}</strong></td>
                                @foreach($analysis['timeline'] as $event)
                                    @php
                                        $point = collect($evolution)->firstWhere('sequence', $event['sequence']);
                                    @endphp
                                    <td class="text-center">
                                        @if($point)
                                            <span class="badge bg-secondary" title="{{ $point['type'] }}">
                                                {{ number_format($point['intensity'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- sidebar Analysis -->
    <div class="col-md-4">
        <!-- Collapse Analysis -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                💥 Collapse Analysis
            </div>
            <div class="card-body">
                <h6>Patterns Detected</h6>
                <ul class="list-group list-group-flush mb-3">
                    @forelse($analysis['collapse_analysis']['patterns'] as $pattern)
                        <li class="list-group-item">{{ $pattern }}</li>
                    @empty
                        <li class="list-group-item text-muted">No specific patterns detected</li>
                    @endforelse
                </ul>

                <h6>Common Collapse Triggers</h6>
                <ul class="list-group list-group-flush">
                    @foreach($analysis['collapse_analysis']['common_archetypes'] as $arch => $count)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $arch }}
                            <span class="badge bg-danger rounded-pill">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Myth Patterns -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                🧠 Myth Patterns
            </div>
            <div class="card-body">
                @foreach($analysis['myth_patterns'] as $doctrine => $data)
                    <div class="mb-3">
                        <h6>{{ ucfirst($doctrine) }}</h6>
                        <div class="progress mb-1">
                            <div class="progress-bar" role="progressbar" style="width: {{ min($data['count'] * 20, 100) }}%">
                                {{ $data['count'] }} occurrences
                            </div>
                        </div>
                        <small class="text-muted">
                            Residue: {{ implode(', ', array_unique($data['residue_types'])) }}
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
@endsection
