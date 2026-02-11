@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.material.extraction.index') }}" class="text-blue-600 hover:text-blue-900">
            ← Back to list
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $template->material_template['name'] ?? 'Extraction Template' }}</h1>
                <p class="text-gray-600">
                    Source: <span class="font-mono">{{ $template->source_type }}</span>
                    @if($template->source_url)
                        | <a href="{{ $template->source_url }}" target="_blank" class="text-blue-600">View Source</a>
                    @endif
                </p>
            </div>
            
            <div class="text-right">
                <span class="px-3 py-1 rounded text-sm font-semibold
                    {{ $template->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $template->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $template->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($template->status) }}
                </span>
            </div>
        </div>

        <!-- Validation Status -->
        <div class="mb-6 p-4 rounded {{ $template->isValid() ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <h3 class="font-bold mb-2">Validation Status</h3>
            @if($template->isValid())
                <p class="text-green-700">✓ All validation rules passed</p>
            @else
                <p class="text-red-700 font-semibold mb-2">✗ Validation failed:</p>
                <ul class="list-disc list-inside text-red-600">
                    @foreach($template->getValidationErrors() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @if($template->getValidationWarnings())
                <p class="text-yellow-700 font-semibold mt-3 mb-1">⚠ Warnings:</p>
                <ul class="list-disc list-inside text-yellow-600">
                    @foreach($template->getValidationWarnings() as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Material Template -->
        <div class="mb-6">
            <h3 class="text-xl font-bold mb-3">Material Definition</h3>
            <div class="bg-gray-50 p-4 rounded">
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="font-semibold text-gray-700">Code:</dt>
                        <dd class="font-mono">{{ $template->material_template['code'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-700">Name:</dt>
                        <dd>{{ $template->material_template['name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-700">Ontology:</dt>
                        <dd>{{ $template->material_template['ontology'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-700">Function:</dt>
                        <dd>{{ $template->material_template['function'] ?? 'N/A' }}</dd>
                    </div>
                </dl>

                <div class="mt-4">
                    <dt class="font-semibold text-gray-700 mb-1">Description:</dt>
                    <dd class="text-gray-600">{{ $template->material_template['description'] ?? 'N/A' }}</dd>
                </div>

                <div class="mt-4">
                    <dt class="font-semibold text-gray-700 mb-1">Pressure Outputs:</dt>
                    <dd><pre class="bg-white p-2 rounded text-sm">{{ json_encode($template->material_template['pressure_outputs'] ?? [], JSON_PRETTY_PRINT) }}</pre></dd>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($template->status === 'pending')
            <div class="flex gap-4">
                <form action="{{ route('admin.material.extraction.approve', $template) }}" method="POST" class="flex-1">
                    @csrf
                    <textarea name="notes" placeholder="Approval notes (optional)" class="w-full p-2 border rounded mb-2" rows="2"></textarea>
                    <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                        ✓ Approve & Create Material
                    </button>
                </form>

                <form action="{{ route('admin.material.extraction.reject', $template) }}" method="POST" class="flex-1">
                    @csrf
                    <textarea name="notes" placeholder="Rejection reason (required)" class="w-full p-2 border rounded mb-2" rows="2" required></textarea>
                    <button type="submit" class="w-full bg-red-600 text-white px-6 py-3 rounded hover:bg-red-700">
                        ✗ Reject Template
                    </button>
                </form>
            </div>
        @endif

        @if($template->notes)
            <div class="mt-6 p-4 bg-gray-50 rounded">
                <h4 class="font-semibold mb-2">Notes:</h4>
                <p class="text-gray-700">{{ $template->notes }}</p>
                @if($template->approver)
                    <p class="text-sm text-gray-500 mt-2">
                        By {{ $template->approver->name }} on {{ $template->approved_at->format('Y-m-d H:i') }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
