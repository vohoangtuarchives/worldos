@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Material Extraction Templates</h1>
        
        <div class="flex gap-2">
            <a href="{{ route('admin.material.extraction.index', ['status' => 'pending']) }}" 
               class="px-4 py-2 rounded {{ $status === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                Pending
            </a>
            <a href="{{ route('admin.material.extraction.index', ['status' => 'approved']) }}" 
               class="px-4 py-2 rounded {{ $status === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                Approved
            </a>
            <a href="{{ route('admin.material.extraction.index', ['status' => 'rejected']) }}" 
               class="px-4 py-2 rounded {{ $status === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                Rejected
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $template->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 bg-gray-100 rounded">{{ $template->source_type }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                            {{ $template->material_template['code'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($template->isValid())
                                <span class="text-green-600">✓ Valid</span>
                            @else
                                <span class="text-red-600">✗ Invalid</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $template->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.material.extraction.show', $template) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No templates found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $templates->links() }}
    </div>
</div>
@endsection
