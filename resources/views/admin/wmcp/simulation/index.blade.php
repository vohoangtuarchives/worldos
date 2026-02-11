@extends('layouts.admin')

@section('content')
<h2>Simulation Control</h2>

<div class="row">
    @foreach($worlds as $world)
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ $world->name }}</span>
                <span class="badge bg-secondary">Tick: {{ $world->clock->current_tick ?? 0 }}</span>
            </div>
            <div class="card-body">
                <p>Profile: <strong>{{ $world->law_profile->magicSystem->value }}</strong></p>
                <form action="{{ route('admin.wmcp.simulation.run', $world->id) }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="number" name="steps" class="form-control" value="1" min="1" max="10">
                        <button class="btn btn-primary" type="submit">Run Steps</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
