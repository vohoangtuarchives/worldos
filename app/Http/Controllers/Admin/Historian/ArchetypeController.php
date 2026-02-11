<?php

namespace App\Http\Controllers\Admin\Historian;

use App\Http\Controllers\Controller;
use App\Domains\Historian\ArchetypeAnalyzer;
use App\Domains\CognitiveKernel\ArchetypePool;

class ArchetypeController extends Controller
{
    private ArchetypeAnalyzer $analyzer;
    private ArchetypePool $pool;

    public function __construct(ArchetypeAnalyzer $analyzer, ArchetypePool $pool)
    {
        $this->analyzer = $analyzer;
        $this->pool = $pool;
    }

    public function index()
    {
        $archetypes = $this->pool->getAll();
        $analytics = [];

        foreach ($archetypes as $arch) {
            $analytics[] = $this->analyzer->analyzeArchetype($arch['key']);
        }

        return view('admin.historian.archetypes.index', compact('archetypes', 'analytics'));
    }

    public function show(string $key)
    {
        $archetype = $this->pool->get($key);
        if (!$archetype) {
            abort(404, 'Archetype not found');
        }

        $analytics = $this->analyzer->analyzeArchetype($key);
        
        // Find sagas where this archetype was dominant
        // Ideally this would come from a dedicated query for performance
        $relatedSagas = \App\Domains\Saga\Saga::where('status', 'completed')
            ->take(5) // Optimization: just take recent 5 for now
            ->get();

        return view('admin.historian.archetypes.show', compact('archetype', 'analytics', 'relatedSagas'));
    }
}
