<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Narrative\Serial\SerialStoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate one next chapter for a series. Used for batch: dispatch N times (e.g. chain) to generate N chapters.
 * Only one job per series at a time (ShouldBeUnique) to avoid race on NarrativeState.
 */
class GenerateSerialChapterJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 300;

    public function __construct(
        private readonly string $seriesId
    ) {
    }

    public function uniqueId(): string
    {
        return 'generate_serial_chapter:' . $this->seriesId;
    }

    public function handle(SerialStoryService $serialStoryService): void
    {
        try {
            $serialStoryService->generateNextChapter($this->seriesId);
        } catch (\Throwable $e) {
            Log::error('GenerateSerialChapterJob failed', [
                'series_id' => $this->seriesId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
