<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use App\Domains\Material\Material;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of materials.
     */
    public function index(Request $request)
    {
        $query = Material::query();

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Ontology filter
        if ($request->has('ontology')) {
            $query->where('ontology', $request->input('ontology'));
        }

        // Function filter
        if ($request->has('function')) {
            $query->where('function', $request->input('function'));
        }

        $materials = $query->paginate(20);

        return view('admin.wmcp.materials.index', [
            'materials' => $materials,
        ]);
    }

    /**
     * Show the form for creating a new material.
     */
    public function create()
    {
        return view('admin.wmcp.materials.create');
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:materials,code',
            'ontology' => 'required|in:symbolic,institutional,behavioral',
            'function' => 'required|in:legitimizing,stabilizing,transformative,destructive',
            'default_lifecycle' => 'required|in:dormant,active,decaying,legacy',
            'description' => 'required|string',
            'origin_sources' => 'nullable|array',
            'preconditions' => 'nullable|array',
            'pressure_inputs' => 'nullable|array',
            'pressure_outputs' => 'nullable|array',
            'incompatible_with' => 'nullable|array',
            'mutation_axes' => 'nullable|array',
        ]);

        $material = $this->repository->create($validated);

        return redirect()->route('admin.materials.show', $material->id)
            ->with('success', 'Material created successfully.');
    }

    /**
     * Display the specified material.
     */
    public function show(string $id)
    {
        $material = Material::findOrFail($id);
        $instances = $material->instances()->with('world')->get();

        return view('admin.wmcp.materials.show', [
            'material' => $material,
            'instances' => $instances,
        ]);
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(string $id)
    {
        $material = Material::findOrFail($id);

        return view('admin.wmcp.materials.edit', [
            'material' => $material,
        ]);
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, string $id)
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
            'origin_sources' => 'nullable|array',
            'preconditions' => 'nullable|array',
            'pressure_inputs' => 'nullable|array',
            'pressure_outputs' => 'nullable|array',
            'incompatible_with' => 'nullable|array',
            'mutation_axes' => 'nullable|array',
        ]);

        $material->update($validated);

        return redirect()->route('admin.materials.show', $material->id)
            ->with('success', 'Material updated successfully.');
    }

    /**
     * Remove the specified material.
     */
    public function destroy(string $id)
    {
        $material = Material::findOrFail($id);
        
        // Check if material has instances
        if ($material->instances()->count() > 0) {
            return back()->with('error', 'Cannot delete material with existing instances.');
        }

        $material->delete();

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material deleted successfully.');
    }

    /**
     * Show compatibility editor.
     */
    public function editCompatibility()
    {
        $materials = Material::all();

        return view('admin.wmcp.materials.compatibility', [
            'materials' => $materials,
        ]);
    }

    /**
     * Update compatibility matrix.
     */
    public function updateCompatibility(Request $request)
    {
        $incompatibleData = $request->input('incompatible', []);

        foreach ($incompatibleData as $materialId => $incompatibleCodes) {
            $material = Material::find($materialId);
            if ($material) {
                $material->update([
                    'incompatible_with' => $incompatibleCodes,
                ]);
            }
        }

        return redirect()->route('admin.materials.compatibility')
            ->with('success', 'Compatibility matrix updated successfully.');
    }
}
