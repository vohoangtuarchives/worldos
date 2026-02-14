@extends('layouts.admin')

@section('title', 'Edit Material: ' . $material->code)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Material: {{ $material->code }}</h2>
        <a href="{{ route('admin.materials.show', $material->id) }}" class="btn btn-secondary">Cancel</a>
    </div>

    <form action="{{ route('admin.materials.update', $material->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Material Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" value="{{ $material->code }}" disabled>
                            <small class="text-muted">Code cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ontology</label>
                            <input type="text" class="form-control" value="{{ $material->ontology->value }}" disabled>
                            <small class="text-muted">Ontology cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Function</label>
                            <input type="text" class="form-control" value="{{ $material->function->value }}" disabled>
                            <small class="text-muted">Function cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $material->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Origin Sources</label>
                            <textarea name="origin_sources" class="form-control" rows="3" placeholder='["source1", "source2"]'>{{ json_encode($material->origin_sources ?? []) }}</textarea>
                            <small class="text-muted">JSON array format</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preconditions</label>
                            <textarea name="preconditions" class="form-control" rows="3" placeholder='{"condition": "value"}'>{{ json_encode($material->preconditions ?? []) }}</textarea>
                            <small class="text-muted">JSON object format</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pressure Inputs</label>
                            <textarea name="pressure_inputs" class="form-control" rows="3" placeholder='{"input": 0.5}'>{{ json_encode($material->pressure_inputs ?? []) }}</textarea>
                            <small class="text-muted">JSON object format</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pressure Outputs</label>
                            <textarea name="pressure_outputs" class="form-control" rows="3" placeholder='{"output": 0.5}'>{{ json_encode($material->pressure_outputs ?? []) }}</textarea>
                            <small class="text-muted">JSON object format</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Incompatible With</label>
                            <textarea name="incompatible_with" class="form-control" rows="2" placeholder='["MATERIAL_CODE1", "MATERIAL_CODE2"]'>{{ json_encode($material->incompatible_with ?? []) }}</textarea>
                            <small class="text-muted">JSON array of material codes</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mutation Axes</label>
                            <textarea name="mutation_axes" class="form-control" rows="2" placeholder='["axis1", "axis2"]'>{{ json_encode($material->mutation_axes ?? []) }}</textarea>
                            <small class="text-muted">JSON array format</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.materials.show', $material->id) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Material</button>
        </div>
    </form>
</div>
@endsection
