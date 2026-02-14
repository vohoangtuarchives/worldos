@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1>Edit World Laws: {{ $world->name }}</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.wmcp.worlds.update_laws', $world->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Magic System</label>
                            <select name="magic_system" class="form-select">
                                @foreach(\App\Domains\World\Enums\MagicSystemType::cases() as $case)
                                    <option value="{{ $case->value }}" {{ $world->law_profile->magicSystem === $case ? 'selected' : '' }}>
                                        {{ $case->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Power Ceiling</label>
                            <select name="power_ceiling" class="form-select">
                                @foreach(\App\Domains\World\Enums\PowerCeiling::cases() as $case)
                                    <option value="{{ $case->value }}" {{ $world->law_profile->powerCeiling === $case ? 'selected' : '' }}>
                                        {{ $case->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Technology Level</label>
                            <select name="tech_level" class="form-select">
                                @foreach(\App\Domains\World\Enums\TechLevel::cases() as $case)
                                    <option value="{{ $case->value }}" {{ $world->law_profile->techLevel === $case ? 'selected' : '' }}>
                                        {{ $case->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="cultivation_allowed" id="cultivation" 
                        {{ $world->law_profile->cultivationAllowed ? 'checked' : '' }}>
                    <label class="form-check-label" for="cultivation">Cultivation Allowed</label>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="myth_emergence" id="myth"
                        {{ $world->law_profile->mythEmergenceEnabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="myth">Myth Emergence Enabled</label>
                </div>

                <button type="submit" class="btn btn-primary">Update Laws</button>
            </form>
        </div>
    </div>
</div>
@endsection
