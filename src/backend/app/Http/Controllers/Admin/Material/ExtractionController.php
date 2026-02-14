<?php

namespace App\Http\Controllers\Admin\Material;

use App\Http\Controllers\Controller;
use App\Models\MaterialExtractionTemplate;
use App\Domains\Material\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtractionController extends Controller
{
    /**
     * List extraction templates.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $templates = MaterialExtractionTemplate::query()
            ->when($status === 'pending', fn($q) => $q->pending())
            ->when($status === 'approved', fn($q) => $q->approved())
            ->when($status === 'rejected', fn($q) => $q->rejected())
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.material.extraction.index', compact('templates', 'status'));
    }

    /**
     * Show template details.
     */
    public function show(MaterialExtractionTemplate $template)
    {
        return view('admin.material.extraction.show', compact('template'));
    }

    /**
     * Approve template and create material.
     */
    public function approve(Request $request, MaterialExtractionTemplate $template)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $template->approve(Auth::id(), $request->input('notes'));

        // Create material from template
        Material::create($template->material_template);

        return redirect()
            ->route('admin.material.extraction.index')
            ->with('success', 'Template approved and material created');
    }

    /**
     * Reject template.
     */
    public function reject(Request $request, MaterialExtractionTemplate $template)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $template->reject(Auth::id(), $request->input('notes'));

        return redirect()
            ->route('admin.material.extraction.index')
            ->with('success', 'Template rejected');
    }
}
