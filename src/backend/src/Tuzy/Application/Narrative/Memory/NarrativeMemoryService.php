<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Memory;

use Tuzy\Domain\Narrative\ValueObject\MemorySnapshot;
use App\Models\NarrativeSeries;
use App\Models\SerialChapter;
use Tuzy\Application\Narrative\Serial\Contracts\StructuredSummaryGeneratorInterface;
use Illuminate\Support\Str;

/**
 * Builds MemorySnapshot for a series at "before next chapter": digest + last paragraphs.
 */
class NarrativeMemoryService
{
    private const LAST_PARAGRAPHS_WORD_LIMIT = 150;
    private const DIGEST_SUMMARY_LIMIT = 200;

    public function __construct(
        private readonly ?StructuredSummaryGeneratorInterface $structuredSummaryGenerator = null
    ) {
    }

    /**
     * Build MemorySnapshot for the next chapter (before it is generated).
     *
     * @param int $nextChapterIndexInArc 0-based chapter index within current arc
     * @param int $chaptersPerArc
     * @param string $lastBeat Emotional beat of the previous chapter (from fingerprint or BeatSpec)
     */
    public function snapshotForSeries(
        NarrativeSeries $series,
        int $nextChapterIndexInArc,
        int $chaptersPerArc,
        string $lastBeat = ''
    ): MemorySnapshot {
        $totalChaptersSoFar = $series->total_chapters_generated;
        $bookIndex = $series->current_book_index;
        $arcProgress = $chaptersPerArc > 0
            ? (float) ($nextChapterIndexInArc / $chaptersPerArc)
            : 0.0;
        $arcProgress = min(1.0, max(0.0, $arcProgress));

        $lastChapter = SerialChapter::where('narrative_series_id', $series->id)
            ->orderBy('book_index', 'desc')
            ->orderBy('chapter_index', 'desc')
            ->first();

        $digest = $this->buildDigest($series, $lastChapter);
        $lastParagraphs = $lastChapter !== null
            ? $this->extractLastParagraphs($lastChapter->content ?? '')
            : '';

        return new MemorySnapshot(
            lastBeat: $lastBeat,
            arcProgress: $arcProgress,
            unresolvedConflicts: [],
            digest: $digest,
            lastParagraphs: $lastParagraphs
        );
    }

    private function buildDigest(NarrativeSeries $series, ?SerialChapter $lastChapter): string
    {
        $chapters = SerialChapter::where('narrative_series_id', $series->id)
            ->orderBy('book_index', 'asc')
            ->orderBy('chapter_index', 'asc')
            ->limit(5)
            ->get();

        if ($chapters->isEmpty()) {
            return '';
        }

        $parts = [];
        foreach ($chapters as $ch) {
            $structured = $this->structuredSummaryGenerator?->generate($ch->content ?? '');
            if ($structured !== null && !empty($structured['main_events'])) {
                $parts[] = implode(' ', array_slice($structured['main_events'], 0, 2));
            } else {
                $parts[] = $ch->summary ?? Str::limit(strip_tags($ch->content ?? ''), 80);
            }
        }
        $combined = implode(' ', $parts);
        return Str::limit($combined, self::DIGEST_SUMMARY_LIMIT);
    }

    private function extractLastParagraphs(string $content): string
    {
        $cleaned = strip_tags($content);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned)) ?? '';
        if ($cleaned === '') {
            return '';
        }
        $words = explode(' ', $cleaned);
        if (count($words) <= self::LAST_PARAGRAPHS_WORD_LIMIT) {
            return $cleaned;
        }
        $tail = array_slice($words, -self::LAST_PARAGRAPHS_WORD_LIMIT);
        return implode(' ', $tail);
    }
}
