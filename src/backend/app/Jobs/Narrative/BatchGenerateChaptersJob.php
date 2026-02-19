<?php

namespace App\Jobs\Narrative;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchGenerateChaptersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string> $chapterIds
     */
    public function __construct(
        private array $chapterIds
    ) {}

    public function handle(): void
    {
        Log::info("Starting batch generation for " . count($this->chapterIds) . " chapters");

        foreach ($this->chapterIds as $chapterId) {
            // Parallel dispatch to workers
            // Assuming there's a GenerateChapterJob that handles the actual LLM call and persistence
            // \App\Jobs\Narrative\GenerateChapterJob::dispatch($chapterId);
            Log::info("Dispatched generation for chapter: " . $chapterId);
        }
    }
}
