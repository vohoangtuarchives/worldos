@extends('layouts.admin')

@section('title', $material->code)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $material->code }}</h2>
        <div>
            <a href="{{ route('admin.materials.edit', $material->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    {{-- Material Details --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Material Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Code:</th>
                            <td><code>{{ $material->code }}</code></td>
                        </tr>
                        <tr>
                            <th>Ontology:</th>
                            <td><span class="badge bg-info">{{ $material->ontology->value }}</span></td>
                        </tr>
                        <tr>
                            <th>Function:</th>
                            <td><span class="badge bg-secondary">{{ $material->function->value }}</span></td>
                        </tr>
                        <tr>
                            <th>Default Lifecycle:</th>
                            <td><span class="badge bg-success">{{ $material->default_lifecycle->value }}</span></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $material->description }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Configuration</h5>
                </div>
                <div class="card-body">
                    <h6>Origin Sources</h6>
                    <ul>
                        @forelse($material->origin_sources ?? [] as $source)
                            <li>{{ $source }}</li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>

                    <h6>Incompatible With</h6>
                    <ul>
                        @forelse($material->incompatible_with ?? [] as $incompatible)
                            <li><code>{{ $incompatible }}</code></li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>

                    <h6>Mutation Axes</h6>
                    <ul>
                        @forelse($material->mutation_axes ?? [] as $axis)
                            <li>{{ $axis }}</li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Material Instances --}}
    <div class="card">
        <div class="card-header">
            <h5>Material Instances ({{ $instances->count() }})</h5>
        </div>
        <div class="card-body">
            @if($instances->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>World</th>
                            <th>Strength</th>
                            <th>Activation Epoch</th>
                            <th>Status</th>
                            <th>Mutations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instances as $instance)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.wmcp.worlds.show', $instance->world_id) }}">
                                        {{ $instance->world->name ?? 'Unknown' }}
                                    </a>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px; width: 100px;">
                                        <div class="progress-bar bg-success" style="width: {{ $instance->strength_level * 10 }}%">
                                            {{ $instance->strength_level }}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $instance->activation_epoch ?? 'Dormant' }}</td>
                                <td>
                                    @if($instance->retired_at)
                                        <span class="badge bg-danger">Retired</span>
                                    @elseif($instance->activation_epoch !== null)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Dormant</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($instance->mutation_state['mutated_from']))
                                        <span class="badge bg-warning">
                                            From: {{ $instance->mutation_state['mutated_from'] }}
                                        </span>
                                    @endif
                                    @if(isset($instance->mutation_state['mutated_to']))
                                        <span class="badge bg-info">
                                            To: {{ $instance->mutation_state['mutated_to'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No instances of this material exist yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
