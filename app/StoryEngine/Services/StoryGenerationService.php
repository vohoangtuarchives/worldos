<?php

namespace App\StoryEngine\Services;

use App\Models\Chapter;
use App\Models\Story;
use App\Models\StorySeed;
use App\Models\World;
use App\StoryEngine\CharacterState;
use App\StoryEngine\RuleApplier;
use App\StoryEngine\Seed;
use App\StoryEngine\SeedPicker;
use App\StoryEngine\SeedTransition;
use App\StoryEngine\WorldState;
use Illuminate\Support\Facades\DB;

class StoryGenerationService
{
    public function __construct(
        protected StoryContentGenerator $generator
    ) {}

    public function initializeStory(World $world, string $title): Story
    {
        return DB::transaction(function () use ($world, $title) {
            $story = Story::create([
                'world_id' => $world->id,
                'title' => $title,
                'status' => 'active',
                'world_state' => new WorldState(), // Initial state
                'character_state' => new CharacterState(), // Initial state
            ]);

            // Create Initial Seed
            StorySeed::create([
                'story_id' => $story->id,
                'type' => SeedTransition::TYPE_POWER_GAP,
                'dimension' => 'personal',
                'severity' => 5,
                'age' => 0,
                'status' => 'active',
            ]);

            return $story;
        });
    }

    public function generateNextChapter(Story $story): ?Chapter
    {
        return DB::transaction(function () use ($story) {
            // 1. Rehydrate Simulator State
            $activeSeeds = $this->getActiveSeeds($story);
            $worldState = $this->hydrateWorldState($story->world_state);
            $characterState = $this->hydrateCharacterState($story->character_state);

            // 2. Pick Seed
            $seedModel = $this->pickSeedModel($activeSeeds);
            if (!$seedModel) {
                 // Fallback if no seeds? Should not happen if initialized correctly.
                 return null;
            }

            $seedObj = $this->toSeedObject($seedModel);

            // 3. Resolve & Transition (Engine Logic)
            $newSeedObjects = SeedTransition::resolve($seedObj);

            // 4. Update Engine State (Apply Rules)
            // We need to map activeSeeds models to objects for RuleApplier?
            // RuleApplier uses object references.
            $activeSeedObjects = array_map(fn($s) => $this->toSeedObject($s), $activeSeeds->all());

            RuleApplier::apply($worldState, $characterState, $activeSeedObjects, $newSeedObjects, $seedObj);
            
            // 5. Persist Changes
            // a. Mark resolved seed
            $seedModel->update(['status' => 'resolved']);

            // b. Create new seeds
            $generatedSeedsData = [];
            foreach ($activeSeedObjects as $obj) {
                // Determine if this is a NEW seed or existing?
                // RuleApplier adds new seeds to the array.
                // We need to diff?
                // Simpler: RuleApplier modified the array. We just need to ensure DB matches.
                // Actually, RuleApplier adds new objects.
                // Strategy: Identify which objects are new.
                
                // Let's filter activeSeedObjects for those that don't match any existing ID?
                // RuleApplier doesn't attach IDs.
                
                // Better: Just iterate $newSeedObjects (from Transition) and see which were KEPT by RuleApplier.
                // RuleApplier adds to $activeSeeds reference.
            }
            
            // Refined Strategy for Persistence:
            // 1. Logic was: RuleApplier filters $newSeeds and adds accepted ones to $activeSeeds.
            // 2. It also manages duplicates (escalation/merge).
            // This is tricky to map back to DB models efficiently.
            
            // Simplified Approach for DB:
            // Just persist whatever RuleApplier outputted as "New Active Seeds" that aren't currently in DB.
            // But RuleApplier might have modified existing seeds (merged severity).
            
            // Let's rewrite Logic Integration for DB:
            
            // 4a. Apply World/Character changes
            $story->update([
                'world_state' => $worldState,
                'character_state' => $characterState,
            ]);

            // 4b. Handle Seeds
            $createdSeedsSnapshot = [];
            
            // Handle new seeds from Transition
            // We verify against RuleApplier logic manually here or trust the outcome?
             
            // Let's interpret RuleApplier's logic step-by-step for DB:
            
            // I. World Friction
             if (in_array($seedObj->type, [SeedTransition::TYPE_POWER_GAP, SeedTransition::TYPE_ESCALATION_DEBT])) {
                 $worldState->publicAwareness += rand(1, 3);
             }
             
             // II. Process New Seeds
             foreach ($newSeedObjects as $newSeedObj) {
                 // Check duplicate in DB
                 $existing = $story->seeds()
                    ->where('status', 'active')
                    ->where('type', $newSeedObj->type)
                    ->where('dimension', $newSeedObj->dimension)
                    ->first();
                    
                 if ($existing) {
                     // Rule 4: Duplicate
                     $currentLevel = $existing->getDimensionLevel(); // Need helper or map
                     // We can use the object logic
                     $existingObj = $this->toSeedObject($existing);
                     
                     if ($existingObj->getDimensionLevel() < 4) {
                         // Escalate
                         $newDim = Seed::getDimensionFromLevel($existingObj->getDimensionLevel() + 1);
                         
                         // Create escalated seed
                         $s = StorySeed::create([
                             'story_id' => $story->id,
                             'type' => $newSeedObj->type,
                             'dimension' => $newDim,
                             'severity' => $newSeedObj->severity + 1,
                             'status' => 'active',
                         ]);
                         $createdSeedsSnapshot[] = $s->id;
                     } else {
                         // Merge Severity
                         $existing->increment('severity', $newSeedObj->severity);
                     }
                 } else {
                     // Create New
                     $s = StorySeed::create([
                         'story_id' => $story->id,
                         'type' => $newSeedObj->type,
                         'dimension' => $newSeedObj->dimension,
                         'severity' => $newSeedObj->severity,
                         'status' => 'active',
                     ]);
                     $createdSeedsSnapshot[] = $s->id;
                 }
             }
             
             // III. Limit Active Seeds (Rule 10)
             $allActive = $story->seeds()->where('status', 'active')->get();
             if ($allActive->count() > 7) {
                 // Sort by score (severity + age) descending
                 // We want to KEEP high scores, remove LOW scores.
                 $sorted = $allActive->sortByDesc(function ($s) {
                     return $s->severity + $s->age;
                 });
                 
                 // Keep top 7
                 $toKeep = $sorted->take(7)->pluck('id');
                 
                 // Discard others
                 StorySeed::where('story_id', $story->id)
                    ->where('status', 'active')
                    ->whereNotIn('id', $toKeep)
                    ->update(['status' => 'discarded']);
             }
             
             // IV. Age all active
             StorySeed::where('story_id', $story->id)
                ->where('status', 'active')
                ->increment('age');
             
             // 0. Age Character Tier (Simulator logic)
             $characterState->chaptersInCurrentTier++;
             
             // Persist State Updates
             $story->world_state = $worldState;
             $story->character_state = $characterState;
             $story->save();

            // 6. Generate AI Content
            // We use the Seed Object ($seedObj) and Story state
            $generatedContent = $this->generator->generate($story, $seedObj);
            $richContent = $generatedContent['rich'] ?? null;
            
            $nextOrder = $story->chapters()->max('order') + 1;
            
            $chapter = Chapter::create([
                'story_id' => $story->id,
                'order' => $nextOrder,
                'title' => $generatedContent['title'],
                'content' => $generatedContent['content'],
                'resolved_seed_id' => $seedModel->id,
                'generated_seeds' => $createdSeedsSnapshot,
                'rich_content' => $richContent,
            ]);

            return $chapter;
        });
    }

    private function getActiveSeeds(Story $story)
    {
        return $story->seeds()->where('status', 'active')->get();
    }

    private function hydrateWorldState($data): WorldState
    {
        if ($data instanceof WorldState) return $data;
        $state = new WorldState();
        if (is_array($data)) {
            $state->tierIndex = $data['tierIndex'] ?? 0;
            $state->publicAwareness = $data['publicAwareness'] ?? 5;
            $state->powerCenters = $data['powerCenters'] ?? 2;
        }
        return $state;
    }

    private function hydrateCharacterState($data): CharacterState
    {
        if ($data instanceof CharacterState) return $data;
        $state = new CharacterState();
        if (is_array($data)) {
            $state->powerTier = $data['powerTier'] ?? 0;
            $state->exposure = $data['exposure'] ?? 0;
            $state->chaptersInCurrentTier = $data['chaptersInCurrentTier'] ?? 0;
        }
        return $state;
    }

    private function pickSeedModel($seeds)
    {
        if ($seeds->isEmpty()) return null;
        
        // Use engine logic: sort by (severity + age) desc
        $sorted = $seeds->sortByDesc(function ($s) {
            return $s->severity + $s->age;
        });
        
        return $sorted->first();
    }

    private function toSeedObject($model): Seed
    {
        $s = new Seed($model->type, $model->dimension, $model->severity);
        $s->age = $model->age;
        return $s;
    }
}
