@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">🧬 Archetype Analytics</h1>
</div>

<div class="card">
    <div class="card-header">Archetype Performance</div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th scope="col">Archetype</th>
                    <th scope="col">Domain</th>
                    <th scope="col">Sagas Analyzed</th>
                    <th scope="col">Appearance Rate</th>
                    <th scope="col">Dominance Rate</th>
                    <th scope="col">Collapse Involvement</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($archetypes as $index => $archetype)
                @php $stats = $analytics[$index]; @endphp
                <tr>
                    <td>
                        <span class="fw-bold">{{ $archetype['key'] }}</span>
                    </td>
                    <td><span class="badge bg-secondary">{{ $archetype['domain'] }}</span></td>
                    <td>{{ $stats['total_analyzed'] }}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                style="width: {{ $stats['appearance_rate'] * 100 }}%">
                                {{ number_format($stats['appearance_rate'] * 100, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: {{ $stats['dominance_rate'] * 100 }}%">
                                {{ number_format($stats['dominance_rate'] * 100, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                style="width: {{ $stats['collapse_rate'] * 100 }}%">
                                {{ number_format($stats['collapse_rate'] * 100, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.historian.archetypes.show', $archetype['key']) }}" class="btn btn-sm btn-outline-primary">Deep Dive</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
