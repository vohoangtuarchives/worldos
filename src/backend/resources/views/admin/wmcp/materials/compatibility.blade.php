@extends('layouts.admin')

@section('title', 'Material Compatibility Editor')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Material Compatibility Editor</h2>
        <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary">Back to Materials</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Compatibility Matrix</h5>
            <small class="text-muted">Define which materials are incompatible with each other</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.materials.compatibility.update') }}">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Material</th>
                                @foreach($materials as $material)
                                    <th class="text-center" style="writing-mode: vertical-rl; transform: rotate(180deg);">
                                        <small>{{ $material->code }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $rowMaterial)
                                <tr>
                                    <td><code>{{ $rowMaterial->code }}</code></td>
                                    @foreach($materials as $colMaterial)
                                        <td class="text-center">
                                            @if($rowMaterial->id === $colMaterial->id)
                                                <span class="text-muted">-</span>
                                            @else
                                                @php
                                                    $isIncompatible = in_array($colMaterial->code, $rowMaterial->incompatible_with ?? []);
                                                @endphp
                                                <input type="checkbox" 
                                                       name="incompatible[{{ $rowMaterial->id }}][]" 
                                                       value="{{ $colMaterial->code }}"
                                                       {{ $isIncompatible ? 'checked' : '' }}
                                                       class="form-check-input">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Compatibility Matrix</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Compatibility Legend --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5>Compatibility Rules</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Checked:</strong> Materials are incompatible (cannot coexist strongly)</li>
                <li><strong>Unchecked:</strong> Materials can coexist</li>
                <li><strong>Effect:</strong> When incompatible materials are both active, the stronger one may suppress or deactivate the weaker one</li>
            </ul>

            <h6 class="mt-3">Common Incompatibilities:</h6>
            <ul>
                <li><code>DIVINE_KINGSHIP</code> ↔ <code>DEMOCRATIC_ASSEMBLY</code></li>
                <li><code>GIFT_ECONOMY</code> ↔ <code>MARKET_CITY</code></li>
                <li><code>ORAL_TRADITION</code> ↔ <code>WRITTEN_LAW</code></li>
                <li><code>COSMIC_DUALISM</code> ↔ <code>MONOTHEISM</code></li>
            </ul>
        </div>
    </div>
</div>
@endsection
