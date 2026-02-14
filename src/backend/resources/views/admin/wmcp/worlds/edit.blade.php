@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit World Metadata - {{ $world->name }}</h1>
    <a href="{{ route('admin.wmcp.worlds.show', $world->id) }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="alert alert-info mb-4">
    <strong>Note:</strong> Only description and tags can be edited. Type and Law Profile require governance approval to change.
</div>

<form method="POST" action="{{ route('admin.wmcp.worlds.update', $world->id) }}">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header"><strong>Metadata</strong></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">World Type (Read-Only)</label>
                <input type="text" class="form-control" value="{{ $world->type?->label() ?? 'N/A' }}" disabled>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="5">{{ old('description', $world->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma-separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" 
                       value="{{ old('tags', is_array($world->tags) ? implode(', ', $world->tags) : '') }}">
                <small class="text-muted">Current: 
                    @if($world->tags)
                        @foreach($world->tags as $tag)
                            <span class="badge bg-secondary">{{ $tag }}</span>
                        @endforeach
                    @else
                        None
                    @endif
                </small>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="{{ route('admin.wmcp.worlds.show', $world->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
@endsection
