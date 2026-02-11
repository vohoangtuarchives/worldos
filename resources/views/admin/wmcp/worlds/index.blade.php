@extends('layouts.admin')

@section('content')
<h2>Worlds</h2>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Current Tick</th>
                <th>Magic System</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($worlds as $world)
            <tr>
                <td>{{ $world->id }}</td>
                <td>{{ $world->name }}</td>
                <td>{{ $world->clock->current_tick ?? 0 }}</td>
                <td>{{ $world->law_profile->magicSystem->value ?? 'N/A' }}</td>
                <td>Active</td>
                <td>
                    <a href="{{ route('admin.wmcp.worlds.show', $world->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
