<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Domains\WriterConsole\WriterFacingAPI;
use App\Domains\WriterConsole\HumanActionValidator;
use App\Domains\Saga\Saga;
use App\Domains\CognitiveKernel\ArchetypePool;
use Illuminate\Http\Request;

class WriterConsoleController extends Controller
{
    private WriterFacingAPI $api;
    private HumanActionValidator $validator;
    private ArchetypePool $archetypePool;

    public function __construct(
        WriterFacingAPI $api, 
        HumanActionValidator $validator,
        ArchetypePool $archetypePool
    ) {
        $this->api = $api;
        $this->validator = $validator;
        $this->archetypePool = $archetypePool;
    }

    /**
     * Show World Creation Interface
     */
    public function index()
    {
        $archetypes = $this->archetypePool->getAll();
        
        // Map archetypes to writer-friendly terms for UI
        $themes = $archetypes->map(function($a) {
            return [
                'key' => $a->key,
                'name' => ucwords(str_replace('_', ' ', $a->key)),
                'description' => $a->description,
                'domain' => $a->domain
            ];
        })->groupBy('domain');

        return view('writer.console.dashboard', [
            'themes' => $themes,
            'recent_sagas' => Saga::latest()->take(5)->get()
        ]);
    }

    /**
     * Terminology Guide
     */
    public function terminology()
    {
        return view('writer.console.terminology');
    }
}
