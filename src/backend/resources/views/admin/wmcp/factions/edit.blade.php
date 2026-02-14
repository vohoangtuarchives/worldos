@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1>Edit Faction: {{ $faction->name }}</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.wmcp.factions.update', $faction) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Faction Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $faction->name }}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="Sect" {{ $faction->type == 'Sect' ? 'selected' : '' }}>Sect</option>
                        <option value="Clan" {{ $faction->type == 'Clan' ? 'selected' : '' }}>Clan</option>
                        <option value="Guild" {{ $faction->type == 'Guild' ? 'selected' : '' }}>Guild</option>
                        <option value="Kingdom" {{ $faction->type == 'Kingdom' ? 'selected' : '' }}>Kingdom</option>
                        <option value="Organization" {{ $faction->type == 'Organization' ? 'selected' : '' }}>Organization</option>
                    </select>
                </div>
                
                @php
                    $attrs = $faction->attributes ?? [];
                @endphp
                
                <div class="mb-3">
                    <label class="form-label">Cohesion (0-100)</label>
                    <input type="number" name="attributes[cohesion]" class="form-control" value="{{ $attrs['cohesion'] ?? 80 }}" min="0" max="100">
                </div>

                <button type="submit" class="btn btn-primary">Update Faction</button>
            </form>
        </div>
    </div>
</div>
@endsection
