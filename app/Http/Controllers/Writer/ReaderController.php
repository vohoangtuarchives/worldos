<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Reader\ReaderInteractionService;
use App\Domains\Material\Engine\MaterialLawEngine;
use App\Domains\Saga\SagaExecutor;
use App\Models\World;
use App\Domains\Material\State\WorldStateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Domains\Genre\NarrativeTransformer;
use App\Domains\Genre\GenreRegistry;
use App\Narrative\Constraints\NarrativeConstraintLayer;
use App\Narrative\Constraints\LanguageConstraint;
use App\Narrative\Constraints\HonorificConstraint;
use App\Narrative\Constraints\GenreConstraint;
use App\Narrative\Constraints\TemporalConsistencyConstraint;
use App\Narrative\Exceptions\NarrativeViolationException;

class ReaderController extends Controller
{
    public function __construct(
        private ReaderInteractionService $interactionService,
        private MaterialLawEngine $simulationEngine,
        private SagaExecutor $sagaExecutor,
        private WorldStateRepository $stateRepo,
        private NarrativeTransformer $transformer,
        private GenreRegistry $genreRegistry
    ) {}

    public function index(int $worldId)
    {
        $world = World::find($worldId);
        if (!$world) abort(404);

        $currentEpoch = $this->getCurrentEpoch($worldId);
        
        // Get narrative history
        $rawNarrative = DB::table('chronicles')
            ->where('world_id', $worldId)
            ->orderBy('epoch', 'desc')
            ->get();
            
        // 1. Enforce Continuity Policy (No-Read-Backward)
        $policy = new \App\Domains\Saga\ContinuityPolicy();
        $filteredNarrative = $policy->enforceNoReadBackward($currentEpoch, $rawNarrative->toArray());
        
        $narrativeCollection = collect($filteredNarrative)->take(5);

        // Transform narrative based on genre (Stored in world)
        $genreKey = $world->genre ?? 'historical';
        
        $narrative = $narrativeCollection->map(function ($event) use ($genreKey) {
            $event->content = $this->transformer->transform($event->content, $genreKey);
            return $event;
        });

        $choices = $this->interactionService->getChoices($worldId);

        return view('writer.saga.interact', [
            'world' => $world,
            'epoch' => $currentEpoch,
            'narrative' => $narrative,
            'choices' => $choices,
            'genre' => $genreKey
        ]);
    }
    
    // ... rest of file


    public function processChoice(Request $request, int $worldId)
    {
        $validated = $request->validate([
            'choice_id' => 'required|string',
            'option_id' => 'required|string',
        ]);

        // 1. Apply Choice
        $choices = $this->interactionService->getChoices($worldId);
        $this->interactionService->applyChoice(
            $worldId,
            $validated['choice_id'],
            $validated['option_id'],
            $choices
        );

        // 2. Advance Simulation & Generate Narrative
        $this->advanceTimestamp($worldId);

        return redirect()->route('reader.interact', $worldId)
            ->with('success', 'Destiny updated.');
    }

    public function advance(int $worldId)
    {
        $this->advanceTimestamp($worldId);

        return redirect()->route('reader.interact', $worldId);
    }

    private function advanceTimestamp(int $worldId): void
    {
        $nextEpoch = $this->getCurrentEpoch($worldId) + 1;

        // 1. Run Tick
        $this->simulationEngine->processTick($worldId, $nextEpoch);

        // 2. Generate Narrative (Saga)
        // We need previous state vs current state to detect changes
        $previousState = $this->stateRepo->reconstructState($worldId, $nextEpoch - 1);
        $currentState = $this->stateRepo->getCurrentState($worldId);

        $result = $this->sagaExecutor->execute($previousState, $currentState, $nextEpoch);

        // 3. Validate Events (Genre Physics Check)
        // Fetch genre from world, default to historical/generic if not set
        $genreKey = $currentState->world_genre ?? 'historical'; 
        // Note: WorldState might not have genre property yet if not hydrated. 
        // Better to pass it from caller or fetch from World model context if available here.
        // Actually ReaderController has $worldId, can re-fetch or pass it. 
        // But SagaExecutor result doesn't explicitly return genre. 
        // Let's use the one from index() logic or re-fetch.
        $world = World::find($worldId);
        $genreKey = $world->genre ?? 'historical';

        $genre = $this->genreRegistry->get($genreKey);
        
        $validationWarnings = [];
        
        if ($genre) {
            $validator = $genre->validator();
            foreach ($result['events'] as $eventData) {
                // Map raw event to StoryEvent DTO
                // Assuming eventData has 'type' and internal data is payload
                $eventType = $eventData['type'] ?? 'generic';
                $payload = $eventData;
                unset($payload['type']);
                
                $storyEvent = new \App\Domains\Genre\DTO\StoryEvent($eventType, $payload);
                
                $validation = $validator->validateEvent($genre, $currentState, $storyEvent);
                
                if (!$validation->valid) {
                    foreach ($validation->violations as $violation) {
                        $reason = is_object($violation) ? $violation->reason : 'Unknown violation';
                        $validationWarnings[] = "[PHYSICS VIOLATION] $reason";
                    }
                }
            }
        }

        // 4. Resolve World State (Emergent Properties)
        $powerState = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $powerStage = \App\Domains\Power\Enums\PowerStage::from($powerState?->current_stage ?? 'mundane');
        
        $saga = DB::table('sagas')->where('world_id', $worldId)->first(); // Assuming 1 saga for simplicity now
        $powerScope = \App\Domains\Saga\Enums\PowerScope::from($saga?->power_scope ?? 'local');

        $ledgerRepo = new \App\Domains\Power\Repositories\WorldEventLedgerRepository();
        $history = $ledgerRepo->getHistory($worldId, 10);
        $phaseRule = new \App\Domains\Power\TransitionNarrativeRule();
        $phase = $phaseRule->getPhase($worldId, $nextEpoch, $history);

        // 5. Validate Narrative Tone (Narrative Guard)
        $context = new \App\Narrative\Values\NarrativeContext(
            targetLanguage: 'vi',
            tone: 'han-viet',
            audience: 'human_reader',
            genre: $genre,
            powerStage: $powerStage,
            powerScope: $powerScope,
            phase: $phase
        );

        $pipeline = new \App\Narrative\Validation\NarrativeValidationPipeline();
        $pipeline->addValidator(new \App\Narrative\Validation\HonorificValidator());
        $pipeline->addValidator(new \App\Narrative\Validation\LanguageToneValidator());

        // Get content string
        $narrativeText = is_array($result['narrative']) ? implode("\n\n", $result['narrative']) : $result['narrative'];
        if (empty($narrativeText)) {
            $narrativeText = "The epoch passes without significant event.";
        }

        $narrativeValidation = $pipeline->run($narrativeText, $context);

        // 6. Inject Legends (Saga Context Enrichment)
        $legendCapsule = [];
        $legendInjector = new \App\Domains\Saga\LegendInjector($ledgerRepo);
        $legendInjector->inject($worldId, $legendCapsule);
        
        // Note: In a real LLM call, $legendCapsule would be merged into the prompt.
        // For the current controller, we've demonstrated the retrieval logic.

        if (!$narrativeValidation->valid) {
            foreach ($narrativeValidation->violations as $violation) {
                $validationWarnings[] = "[STYLE VIOLATION] $violation";
            }
        }

        // 5. Enforce Narrative Constraints (Hard Gate)
        $registry = new \App\Narrative\Constraints\ConstraintRegistry([
            base_path('resources/narrative/constraints/v1')
        ]);
        
        $constraintLayer = new \App\Narrative\Constraints\NarrativeConstraintLayer($registry);
        // Hard-coded constraints can stay for complex logic not yet in DSL, 
        // but simple word blocks are now in YAML.
        $constraintLayer->addConstraint(new \App\Narrative\Constraints\TemporalConsistencyConstraint());

        try {
            $constraintLayer->enforce($context, $narrativeText);
        } catch (NarrativeViolationException $e) {
            $validationWarnings[] = "[FATAL CONSTRAINT VIOLATION] " . $e->getMessage();
            // In a production AI loop, this would trigger a re-prompt.
            // For now, we flag it so the reader/writer knows the generation failed quality checks.
        }

        // 6. Save Chronicle
        $content = $narrativeText;
        
        // Append warnings if any
        if (!empty($validationWarnings)) {
            $content .= "\n\n" . implode("\n", $validationWarnings);
        }

        DB::table('chronicles')->insert([
            'world_id' => $worldId,
            'epoch' => $nextEpoch,
            'content' => $content,
            'events' => json_encode($result['events']),
            'created_at' => now(),
        ]);
    }

    private function getCurrentEpoch(int $worldId): int
    {
        return DB::table('world_state_events')
            ->where('world_id', $worldId)
            ->max('epoch') ?? 0;
    }
}
