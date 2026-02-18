<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Narrative\Serial\SerialStoryService;
use App\Models\NarrativeSeries;
use Illuminate\Console\Command;

/**
 * Generate next N chapters for a series (synchronous). For async batch use API POST /serial/series/{id}/generate-chapters.
 */
class SerialGenerateChaptersCommand extends Command
{
    protected $signature = 'serial:generate-chapters
                            {series_id : Narrative series UUID}
                            {--count=1 : Number of chapters to generate (1-20)}';

    protected $description = 'Generate next N chapters for a serial series (sync). Use API for async batch.';

    public function handle(SerialStoryService $serialStoryService): int
    {
        $seriesId = $this->argument('series_id');
        $count = (int) $this->option('count');
        if ($count < 1 || $count > 20) {
            $this->error('Count must be between 1 and 20.');
            return self::FAILURE;
        }
        $series = NarrativeSeries::find($seriesId);
        if ($series === null) {
            $this->error("Series {$seriesId} not found.");
            return self::FAILURE;
        }
        $this->info("Generating up to {$count} chapter(s) for series: {$series->title}");
        $generated = 0;
        for ($i = 0; $i < $count; $i++) {
            $chapter = $serialStoryService->generateNextChapter($seriesId);
            if ($chapter === null) {
                $this->warn('No more chapters to generate (arc plan exhausted).');
                break;
            }
            $generated++;
            $this->line("  Chapter {$generated}: book {$chapter->book_index}, index {$chapter->chapter_index}");
        }
        $this->info("Done. Generated {$generated} chapter(s).");
        return self::SUCCESS;
    }
}
