<?php

namespace Tuzy\Application\Narrative\Services;

use App\Models\SerialChapter;
use App\Models\UniverseModel;
use App\Models\WorldMyth;
use Tuzy\Domain\Narrative\Events\MythCanonized;
use Illuminate\Support\Facades\Log;

class NarrativeFeedbackService
{
    public function __construct(
        protected NarrativeBridge $narrativeBridge
    ) {}

    /**
     * Process a canonized chapter to extract myths and impact the universe.
     */
    public function processCanonization(SerialChapter $chapter, UniverseModel $universe): void
    {
        // 1. Extract Myth from Chapter Content (Simplified for now, ideally LLM)
        // For now, we assume the chapter title and summary form the myth.
        $mythTitle = $chapter->title;
        $mythDescription = $chapter->summary ?? substr($chapter->content, 0, 200);

        // 2. Determine Material Impact based on Genre
        // e.g. Xianxia -> Increases 'SPIRIT_QI', 'HIERARCHY'
        $impact = $this->calculateMaterialImpact($universe->genre_key ?? 'generic');

        // 3. Create/Update World Myth
        $myth = WorldMyth::create([
            'world_id' => $universe->world_id,
            'origin_universe_id' => $universe->id,
            'name' => $mythTitle,
            'description' => $mythDescription,
            'genre_origin' => $universe->genre_key,
            'status' => 'active',
            'strength' => 0.5, // Initial strength
            'affected_materials' => $impact,
            'canonized_at' => now(),
        ]);

        \Illuminate\Support\Facades\Log::info("Myth Canonized: {$myth->name} from Universe {$universe->id}");

        // 4. Dispatch Event for Physics Engine to pickup
        event(new MythCanonized($myth, $universe)); 
    }

    protected function calculateMaterialImpact(string $genreKey): array
    {
        return match ($genreKey) {
            'xianxia' => [
                'SPIRIT_QI' => 0.1,
                'HIERARCHY' => 0.05,
                'MORTAL_VALUE' => -0.1
            ],
            'cyberpunk' => [
                'INNOVATION' => 0.1,
                'INEQUALITY' => 0.1, 
                'NATURE' => -0.1
            ],
            default => []
        };
    }
}
