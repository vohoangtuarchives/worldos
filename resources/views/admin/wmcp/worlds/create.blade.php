@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">🌍 Create New World</h1>
    <a href="{{ route('admin.wmcp.worlds.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="alert alert-info mb-4">
    <strong>⚖️ Constitution Compliance:</strong> All new Worlds are subject to World Law validation and governance rules.
</div>

<form method="POST" action="{{ route('admin.wmcp.worlds.store') }}">
    @csrf

    <div class="row">
        <!-- Basic Info -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><strong>Basic Information</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">World Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">World Type *</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select Type...</option>
                            @foreach(\App\Domains\World\Enums\WorldType::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('type') == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="World lore, backstory, setting...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (comma-separated)</label>
                        <input type="text" class="form-control" id="tags" name="tags" 
                               value="{{ old('tags') }}" placeholder="e.g., tournament, dao, revenge">
                        <small class="text-muted">Separate multiple tags with commas</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- World Law Profile -->
        <div class="col-md-6">
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10"><strong>🔒 World Law Profile</strong></div>
                <div class="card-body">
                    <div class="alert alert-warning py-2 px-2 small mb-3">
                        <strong>Article I:</strong> World Law is the supreme law. Cannot be violated.
                    </div>

                    <div class="mb-3">
                        <label for="magic_system" class="form-label">Magic System *</label>
                        <select class="form-select" id="magic_system" name="magic_system" required>
                            <option value="CULTIVATION">🔥 Cultivation (Qi, Dao)</option>
                            <option value="ARCANE">✨ Arcane Magic</option>
                            <option value="DIVINE">⚡ Divine Power</option>
                            <option value="NONE">🚫 No Magic</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tech_level" class="form-label">Technology Level *</label>
                        <select class="form-select" id="tech_level" name="tech_level" required>
                            <option value="ANCIENT">⚔️ Ancient (Pre-medieval)</option>
                            <option value="MEDIEVAL">🏰 Medieval</option>
                            <option value="RENAISSANCE">📜 Renaissance</option>
                            <option value="INDUSTRIAL">⚙️ Industrial</option>
                            <option value="MODERN">🏙️ Modern</option>
                            <option value="FUTURISTIC">🚀 Futuristic</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="power_ceiling" class="form-label">Power Ceiling *</label>
                        <select class="form-select" id="power_ceiling" name="power_ceiling" required>
                            <option value="MORTAL">👤 Mortal (Human-level)</option>
                            <option value="HERO">⚔️ Heroic (Superhuman)</option>
                            <option value="LEGEND">🌟 Legendary (Demigod)</option>
                            <option value="DIVINE">✨ Divine (God-tier)</option>
                            <option value="COSMIC">🌌 Cosmic (Universe-shaping)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-primary">🌍 Create World</button>
            <a href="{{ route('admin.wmcp.worlds.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
@endsection
