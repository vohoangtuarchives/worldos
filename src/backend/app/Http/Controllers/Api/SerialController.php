<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Narrative\Events\ChapterGenerated;
use App\Domains\Narrative\Serial\SerialGenrePreset;
use App\Domains\Narrative\Serial\SerialStoryService;
use App\Domains\Narrative\Services\PlotPlannerService;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateSerialChapterJob;
use App\Models\NarrativeArcOutline;
use App\Models\NarrativeSeries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Rule;

/**
 * API truyện dài kỳ (serial): tạo series, xem thông tin, sinh chương tiếp.
 */
class SerialController extends Controller
{
    public function __construct(
        private readonly SerialStoryService $serialService
    ) {
    }

    /**
     * Danh sách series (truyện dài kỳ).
     */
    public function index(): JsonResponse
    {
        $series = NarrativeSeries::orderBy('updated_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'series' => $series->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'genre_key' => $s->genre_key,
                    'universe_id' => $s->universe_id,
                    'current_book_index' => $s->current_book_index,
                    'total_chapters_generated' => $s->total_chapters_generated,
                    'updated_at' => $s->updated_at?->format(\DateTimeInterface::ATOM),
                ]),
            ],
        ]);
    }

    /**
     * Danh sách thể loại hỗ trợ (fantasy_school, wuxia, emergent).
     * emergent = tự phát sinh từ world khi series gắn universe_id.
     */
    public function genres(): JsonResponse
    {
        $presets = [
            SerialGenrePreset::FANTASY_SCHOOL => SerialGenrePreset::get(SerialGenrePreset::FANTASY_SCHOOL),
            SerialGenrePreset::WUXIA => SerialGenrePreset::get(SerialGenrePreset::WUXIA),
            SerialGenrePreset::EMERGENT => SerialGenrePreset::get(SerialGenrePreset::EMERGENT),
        ];
        return response()->json([
            'success' => true,
            'data' => [
                'genres' => SerialGenrePreset::supportedGenres(),
                'presets' => $presets,
                'emergent_description' => 'Tự phát sinh từ world (thể loại và arc do universe quyết định). Gắn universe_id khi tạo series.',
            ],
        ]);
    }

    /**
     * Tạo series mới.
     * Body: title (required), genre_key (optional), universe_id (optional), config (optional).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre_key' => ['nullable', 'string', Rule::in(SerialGenrePreset::supportedGenres())],
            'universe_id' => 'nullable|string|max:36',
            'config' => 'nullable|array',
        ]);

        $universeId = $validated['universe_id'] ?? null;
        // When genre_key is null and universe_id is set, createSeries resolves from World.preset (25 loại truyện) or EMERGENT
        $series = $this->serialService->createSeries([
            'title' => $validated['title'],
            'genre_key' => $validated['genre_key'] ?? null,
            'universe_id' => $universeId,
            'config' => $validated['config'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'series' => [
                    'id' => $series->id,
                    'title' => $series->title,
                    'genre_key' => $series->genre_key,
                    'universe_id' => $series->universe_id,
                    'config' => $series->config,
                    'current_book_index' => $series->current_book_index,
                    'total_chapters_generated' => $series->total_chapters_generated,
                ],
            ],
        ], 201);
    }

    /**
     * Cập nhật config series (quality_pipeline, require_arc_approval, ...). PATCH /serial/series/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $validated = $request->validate([
            'config' => 'nullable|array',
            'config.quality_pipeline' => 'nullable|boolean',
            'config.require_arc_approval' => 'nullable|boolean',
        ]);
        $config = $validated['config'] ?? [];
        $series->update(['config' => array_merge($series->config ?? [], $config)]);
        return response()->json([
            'success' => true,
            'data' => [
                'series' => [
                    'id' => $series->id,
                    'config' => $series->config,
                ],
            ],
        ]);
    }

    /**
     * Chi tiết series + danh sách arc đã plan.
     */
    public function show(string $id): JsonResponse
    {
        $payload = $this->serialService->getSeries($id);
        $series = $payload['series'];
        $chapters = $series->chapters()->orderBy('book_index')->orderBy('chapter_index')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'series' => [
                    'id' => $series->id,
                    'title' => $series->title,
                    'genre_key' => $series->genre_key,
                    'universe_id' => $series->universe_id,
                    'config' => $series->config,
                    'current_book_index' => $series->current_book_index,
                    'total_chapters_generated' => $series->total_chapters_generated,
                ],
                'arcs' => $payload['arcs'],
                'chapters' => $chapters->map(fn ($ch) => [
                    'id' => $ch->id,
                    'book_index' => $ch->book_index,
                    'chapter_index' => $ch->chapter_index,
                    'summary' => $ch->summary,
                    'structured_summary' => $ch->structured_summary,
                    'content' => $ch->content,
                    'needs_review' => $ch->needs_review ?? false,
                    'consistency_notes' => $ch->consistency_notes ?? [],
                    'created_at' => $ch->created_at?->format(\DateTimeInterface::ATOM),
                ]),
            ],
        ]);
    }

    /**
     * Sinh chương tiếp theo. Trả về chương vừa sinh hoặc 404 nếu đã hết.
     */
    public function generateNextChapter(string $id): JsonResponse
    {
        $chapter = $this->serialService->generateNextChapter($id);

        if ($chapter === null) {
            return response()->json([
                'success' => false,
                'message' => 'Series đã hoàn thành tất cả arc (không còn chương để sinh).',
            ], 404);
        }

        event(new ChapterGenerated(
            $chapter->narrative_series_id,
            $chapter->id,
            $chapter->book_index,
            $chapter->chapter_index
        ));

        return response()->json([
            'success' => true,
            'data' => [
                'chapter' => [
                    'id' => $chapter->id,
                    'narrative_series_id' => $chapter->narrative_series_id,
                    'book_index' => $chapter->book_index,
                    'chapter_index' => $chapter->chapter_index,
                    'content' => $chapter->content,
                    'summary' => $chapter->summary,
                    'structured_summary' => $chapter->structured_summary,
                    'needs_review' => $chapter->needs_review ?? false,
                    'consistency_notes' => $chapter->consistency_notes ?? [],
                    'created_at' => $chapter->created_at?->format(\DateTimeInterface::ATOM),
                ],
            ],
        ]);
    }

    /**
     * Batch: dispatch N jobs to generate next N chapters (sequential chain). Body: { "count": N }.
     * POST /serial/series/{id}/generate-chapters
     */
    public function generateChapters(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:20',
        ]);
        $count = (int) $validated['count'];
        $jobs = [];
        for ($i = 0; $i < $count; $i++) {
            $jobs[] = new GenerateSerialChapterJob($id);
        }
        Bus::chain($jobs)->dispatch();
        return response()->json([
            'success' => true,
            'data' => [
                'message' => "Dispatched {$count} chapter generation job(s) for series.",
                'series_id' => $id,
                'count' => $count,
            ],
        ], 202);
    }

    /**
     * List arcs for series (with status when from outline). GET /serial/series/{id}/arcs
     */
    public function arcs(string $id): JsonResponse
    {
        $payload = $this->serialService->getSeries($id);
        return response()->json([
            'success' => true,
            'data' => [
                'arcs' => $payload['arcs'],
            ],
        ]);
    }

    /**
     * Approve arc (allow generation). PUT /serial/series/{id}/arcs/{index}/approve
     */
    public function approveArc(string $id, int $index): JsonResponse
    {
        $outline = NarrativeArcOutline::where('narrative_series_id', $id)
            ->where('level', NarrativeArcOutline::LEVEL_ARC)
            ->where('index', $index)
            ->first();
        if ($outline === null) {
            return response()->json(['success' => false, 'message' => 'Arc outline not found.'], 404);
        }
        $outline->update(['status' => NarrativeArcOutline::STATUS_APPROVED]);
        return response()->json([
            'success' => true,
            'data' => [
                'arc' => [
                    'index' => $outline->index,
                    'title' => $outline->title,
                    'one_line' => $outline->one_line,
                    'status' => $outline->status,
                ],
            ],
        ]);
    }

    /**
     * Reject arc (optional comment). PUT /serial/series/{id}/arcs/{index}/reject
     * Body: { "comment": "optional" }
     */
    public function rejectArc(Request $request, string $id, int $index): JsonResponse
    {
        $outline = NarrativeArcOutline::where('narrative_series_id', $id)
            ->where('level', NarrativeArcOutline::LEVEL_ARC)
            ->where('index', $index)
            ->first();
        if ($outline === null) {
            return response()->json(['success' => false, 'message' => 'Arc outline not found.'], 404);
        }
        $comment = $request->input('comment', '');
        $payload = $outline->payload ?? [];
        if ($comment !== '') {
            $payload['reject_comment'] = $comment;
        }
        $outline->update([
            'status' => NarrativeArcOutline::STATUS_DRAFT,
            'payload' => $payload,
        ]);
        return response()->json([
            'success' => true,
            'data' => [
                'arc' => [
                    'index' => $outline->index,
                    'title' => $outline->title,
                    'status' => $outline->status,
                ],
            ],
        ]);
    }

    /**
     * Generate outline (saga / season / arc) from Story Bible. POST /serial/series/{id}/outline/generate
     */
    public function generateOutline(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $validated = $request->validate([
            'books_count' => 'nullable|integer|min:1|max:20',
        ]);
        $booksCount = (int) ($validated['books_count'] ?? $series->config['books_count'] ?? 7);
        try {
            $outlines = app(PlotPlannerService::class)->generateOutline($id, $booksCount);
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Outline generated.',
                    'arcs' => array_map(fn ($o) => [
                        'id' => $o->id,
                        'level' => $o->level,
                        'index' => $o->index,
                        'title' => $o->title,
                        'one_line' => $o->one_line,
                        'status' => $o->status,
                    ], $outlines),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Generate outline failed: ' . $e->getMessage(),
            ], 502);
        }
    }
}
