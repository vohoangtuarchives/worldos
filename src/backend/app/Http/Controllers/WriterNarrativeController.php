<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\WorldOS\Narrative\Actions\CanonizeChapterAction;
use App\WorldOS\Narrative\Actions\CreateSeriesAction;
use App\WorldOS\Narrative\Actions\GenerateChapterAction;
use App\WorldOS\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\WorldOS\Narrative\Entities\SerialChapterEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\ValueObjects\GenreKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriterNarrativeController extends Controller
{
    /**
     * POST /api/writer/narrative/series
     * Create a NarrativeSeries for a Universe.
     */
    public function createSeries(
        Request $request,
        CreateSeriesAction $action,
    ): JsonResponse {
        $request->validate([
            'universe_id' => 'required|uuid',
            'genre' => 'required|string',
            'title' => 'required|string|max:255',
            'require_arc_approval' => 'boolean',
        ]);

        $series = $action->handle(
            universeId: new UniverseId($request->input('universe_id')),
            genre: GenreKey::from($request->input('genre')),
            title: $request->input('title'),
            requireArcApproval: $request->boolean('require_arc_approval', true),
        );

        return response()->json([
            'status' => 'created',
            'series' => [
                'id' => $series->getId(),
                'universe_id' => $series->getUniverseId()->value,
                'genre' => $series->getGenre()->value,
                'title' => $series->getTitle(),
                'current_book' => $series->getCurrentBookIndex(),
                'is_active' => $series->isActive(),
            ],
        ], 201);
    }

    /**
     * POST /api/writer/narrative/chapter/generate
     * Generate a new chapter via LLMChronicler.
     */
    public function generateChapter(
        Request $request,
        GenerateChapterAction $action,
    ): JsonResponse {
        $request->validate([
            'series_id' => 'required|uuid',
        ]);

        $chapter = $action->handle($request->input('series_id'));

        return response()->json([
            'status' => 'generated',
            'chapter' => [
                'id' => $chapter->getId(),
                'series_id' => $chapter->getSeriesId(),
                'book_index' => $chapter->getBookIndex(),
                'chapter_index' => $chapter->getChapterIndex(),
                'status' => $chapter->getStatus()->value,
                'raw_text' => $chapter->getRawText(),
            ],
        ], 201);
    }

    /**
     * POST /api/writer/narrative/chapter/{id}/canonize
     * Canonize an approved chapter → feedback loop.
     */
    public function canonizeChapter(
        Request $request,
        string $id,
        CanonizeChapterAction $action,
    ): JsonResponse {
        $request->validate([
            'universe_id' => 'required|uuid',
        ]);

        // NOTE: In production, load chapter from DB. For now, validate the request.
        // The actual chapter entity would be loaded from a ChapterRepository.
        $result = $action->handle(
            chapter: SerialChapterEntity::draft(
                seriesId: $request->input('series_id', ''),
                bookIndex: 1,
                chapterIndex: 1,
                rawText: '',
            ),
            universeId: new UniverseId($request->input('universe_id')),
        );

        return response()->json([
            'status' => 'canonized',
            'myths_created' => count($result['myths']),
        ]);
    }

    /**
     * GET /api/writer/narrative/series/{id}
     * Get a series with metadata.
     */
    public function getSeries(
        string $id,
        NarrativeSeriesRepositoryInterface $seriesRepository,
    ): JsonResponse {
        $series = $seriesRepository->findById($id);

        if ($series === null) {
            return response()->json(['error' => 'Series not found'], 404);
        }

        return response()->json([
            'series' => [
                'id' => $series->getId(),
                'universe_id' => $series->getUniverseId()->value,
                'genre' => $series->getGenre()->value,
                'title' => $series->getTitle(),
                'current_book' => $series->getCurrentBookIndex(),
                'total_chapters' => $series->getTotalChaptersGenerated(),
                'require_arc_approval' => $series->requiresArcApproval(),
                'is_active' => $series->isActive(),
            ],
        ]);
    }
}
