@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1>Add Faction to {{ $world->name }}</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.wmcp.factions.store', $world) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Faction Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="Sect">Sect</option>
                        <option value="Clan">Clan</option>
                        <option value="Guild">Guild</option>
                        <option value="Kingdom">Kingdom</option>
                        <option value="Organization">Organization</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Cohesion (0-100)</label>
                    <input type="number" name="attributes[cohesion]" class="form-control" value="80" min="0" max="100">
                </div>

                <button type="submit" class="btn btn-primary">Create Faction</button>
            </form>
        </div>
    </div>
</div>
@endsection
