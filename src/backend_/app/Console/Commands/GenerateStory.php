<?php

namespace App\Console\Commands;

use App\Models\Story;
use App\Models\World;
use App\StoryEngine\Services\StoryGenerationService;
use Illuminate\Console\Command;

class GenerateStory extends Command
{
    protected $signature = 'story:generate {story_id?} {--init} {--chapters=1}';
    protected $description = 'Generate chapters for a story';

    public function handle(StoryGenerationService $service)
    {
        $storyId = $this->argument('story_id');
        
        if ($this->option('init')) {
            $world = World::firstOrCreate(['name' => 'Default World']);
            $story = $service->initializeStory($world, 'Generated Story ' . now()->timestamp);
            $this->info("Initialized Story ID: {$story->id}");
            $storyId = $story->id;
        }

        if (!$storyId) {
            $this->error("Please provide a story ID or use --init");
            return;
        }

        $story = Story::find($storyId);
        if (!$story) {
            $this->error("Story not found.");
            return;
        }

        $count = (int) $this->option('chapters');
        $this->info("Generating {$count} chapters for Story: {$story->title}");
        
        $bar = $this->output->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            $chapter = $service->generateNextChapter($story);
            if (!$chapter) {
                $this->warn("Stopped generation: No active seeds.");
                break;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generation Complete.");
    }
}
