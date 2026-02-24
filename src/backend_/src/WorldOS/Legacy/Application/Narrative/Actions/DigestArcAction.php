<?php

namespace WorldOS\Legacy\Application\Narrative\Actions;

use App\Models\StoryBible;
use App\Models\NarrativeArcOutline;
use Illuminate\Support\Facades\Log;

class DigestArcAction
{
    public function execute(string $arcId): void
    {
        $arc = NarrativeArcOutline::findOrFail($arcId);
        $seriesId = $arc->narrative_series_id;
        
        $bible = StoryBible::firstOrCreate(['narrative_series_id' => $seriesId]);
        
        // Phase 7 Goal: Long-term memory layer
        // We accumulate the "Story So Far" into the synopsis to allow context window reset
        $summary = "Arc Digestion: {$arc->title} (Index: {$arc->index})\n" . ($arc->one_line ?? 'No description');
        
        $bible->synopsis = ($bible->synopsis ? $bible->synopsis . "\n\n" : "") . $summary;
        $bible->save();
        
        Log::info("Arc digested into StoryBible", [
            'arc_id' => $arcId,
            'series_id' => $seriesId
        ]);
    }
}
