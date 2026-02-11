@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Seed Template</h1>
    <a href="{{ route('admin.wmcp.seeds.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.wmcp.seeds.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Name</strong></label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name') }}" 
                               placeholder="e.g., Ancient Prophecy Revealed" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Optional narrative context...">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Type</strong></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select type...</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Dimension</strong></label>
                            <select name="dimension" class="form-select" required>
                                <option value="">Select dimension...</option>
                                @foreach($dimensions as $dim)
                                    <option value="{{ $dim }}" {{ old('dimension') == $dim ? 'selected' : '' }}>
                                        {{ ucfirst($dim) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Scope of impact</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Severity (1-10)</strong></label>
                        <input type="number" name="severity" class="form-control" 
                               value="{{ old('severity', 5) }}" min="1" max="10" required>
                        <small class="text-muted">Higher severity = more urgent/dramatic</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_active" class="form-check-input" 
                               id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active (available for injection)
                        </label>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary">Create Seed Template</button>
                    <a href="{{ route('admin.wmcp.seeds.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
