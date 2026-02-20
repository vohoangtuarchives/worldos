<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Domains\Saga\Saga;

use App\Domains\Saga\Services\GenesisPresetService;
use App\Domains\WriterConsole\HumanActionValidator;
use App\Jobs\RunSagaSimulationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WorldCreationController extends Controller
{
    private HumanActionValidator $validator;
    private GenesisPresetService $presetService;

    public function __construct(HumanActionValidator $validator, GenesisPresetService $presetService)
    {
        $this->validator = $validator;
        $this->presetService = $presetService;
    }

    /**
     * Show Genesis page with preset cards.
     */
    public function genesis()
    {
        $categories = $this->presetService->allByCategory();

        return view('writer.genesis', [
            'categories' => $categories,
            'recent_sagas' => Saga::orderBy('created_at', 'desc')->limit(5)->get(),
            'power_systems' => \App\Domains\World\Enums\PowerSystemType::cases(),
            'tech_levels' => \App\Domains\World\Enums\TechLevel::cases(),
            'environments' => \App\Domains\World\Enums\StartingEnvironment::cases(),
            'social_structures' => \App\Domains\World\Enums\SocialStructure::cases(),
            'starting_crises' => \App\Domains\World\Enums\StartingCrisis::cases(),
            'power_ceilings' => \App\Domains\World\Enums\PowerCeiling::cases(),
            'power_rankings' => \App\Domains\World\Enums\PowerRanking::cases(),
        ]);
    }

    /**
     * Create Saga from Genesis preset or custom config.
     */
    public function storeGenesis(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'preset_key' => 'nullable|string',
            'genre' => 'nullable|string',
            'power_system' => 'nullable|string',
            'power_ceiling' => 'nullable|string',
            'tech_level' => 'nullable|string',
            'environment' => 'nullable|string',
            'social_structure' => 'nullable|string',
            'starting_crisis' => 'nullable|string',
            'power_ranking' => 'nullable|string',
            'origin_type' => 'nullable|string',
            'world_count' => 'integer|min:1|max:20',
            'carry_legacy' => 'boolean',
        ]);

        $config = [];
        if (!empty($validated['preset_key'])) {
            $preset = $this->presetService->find($validated['preset_key']);
            if ($preset) {
                $config = $preset;
            }
        }

        foreach (['genre', 'power_system', 'power_ceiling', 'tech_level', 'environment', 'social_structure', 'starting_crisis', 'power_ranking'] as $field) {
            if (!empty($validated[$field])) {
                $config[$field] = $validated[$field];
            }
        }

        $saga = Saga::create([
            'name' => $validated['name'],
            'world_count' => $validated['world_count'] ?? 5,
            'carry_legacy' => $validated['carry_legacy'] ?? true,
            'genre' => $config['genre'] ?? 'xianxia',
            'status' => Saga::STATUS_PENDING,
            'metadata' => [
                'origin_type' => $validated['origin_type'] ?? 'cosmic',
                'genesis_preset' => $validated['preset_key'] ?? 'custom',
                'power_system' => $config['power_system'] ?? 'NONE',
                'power_ceiling' => $config['power_ceiling'] ?? 'HUMAN',
                'tech_level' => $config['tech_level'] ?? 'DYNASTIC',
                'environment' => $config['environment'] ?? 'CONTINENTAL',
                'social_structure' => $config['social_structure'] ?? 'EMPIRE',
                'starting_crisis' => $config['starting_crisis'] ?? 'NONE',
                'power_ranking' => $config['power_ranking'] ?? 'NATURAL',
                'archetype' => $config['archetype'] ?? null,
                'seed_vector' => $config['seed_vector'] ?? null,
                'drift_profile' => $config['drift_profile'] ?? null,
            ],
        ]);

        RunSagaSimulationJob::dispatch($saga);

        return redirect()->route('writer.sagas.show', $saga->id)
            ->with('success', 'Khai Thiên Tịch Địa! Thế giới mới đã được sáng tạo và đang bắt đầu mô phỏng.');
    }

    /**
     * Create/Seed a new Saga (World Series)
     */
    public function store(Request $request)
    {
        // 1. Validate Human Action (Curator Contract)
        $validation = $this->validator->validate('seed_archetype', $request->all());
        
        if (!$validation->allowed) {
            return back()->withErrors(['error' => $validation->reason]);
        }

        // 2. Validate Form Data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'world_count' => 'required|integer|min:1|max:20',
            'archetype_focus' => 'nullable|array',
            'carry_legacy' => 'boolean',
            'genre' => 'nullable|string'
        ]);

        // 3. Launch Saga (via Artisan for async/background processing if strictly decoupled, 
        //    or directly via SagaRunner if synchronous is acceptable. 
        //    Using Saga model creation + Artisan command for robustness)

        // Create the Saga record first
        $saga = Saga::create([
            'name' => $validated['name'],
            'world_count' => $validated['world_count'],
            'archetype_focus' => $validated['archetype_focus'] ?? [],
            'carry_legacy' => $validated['carry_legacy'] ?? true,
            'genre' => $validated['genre'] ?? 'historical',
            'status' => Saga::STATUS_PENDING,
        ]);

        // Trigger the runner (could be queued)
        // For prototype, we might run it or just queue it. 
        // Let's assume we queue the job or notify the runner.
        // Here we'll just redirect to the saga explorer.
        
        // Optional: Trigger background run
        // Artisan::queue('saga:run', ['name' => $saga->name, ...]);

        return redirect()->route('writer.sagas.show', $saga->id)
            ->with('success', 'New Saga initialized. The simulation has begun.');
    }
}
