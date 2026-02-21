<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Serial;

use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Narrative\Bridge\Contracts\StoryEventExtractorInterface;
use App\Domains\Narrative\Bridge\NarrativeToUniverseAdapter;
use App\Domains\Narrative\Bridge\StateSerializerForPrompt;
use App\Domains\Narrative\Bridge\WorldMutationPolicy;
use Tuzy\Domain\Narrative\ValueObject\BeatSpec;
use App\Domains\Narrative\Services\ChapterCriticService;
use App\Domains\Narrative\Services\ConsistencyValidator;
use App\Domains\Narrative\Services\LorebookResolver;
use App\Domains\Narrative\Memory\NarrativeMemoryService;
use App\Domains\Narrative\Planning\BeatPlanner;
use App\Domains\Narrative\Planning\ChapterProducer;
use App\Domains\Narrative\Planning\LayeredProducer;
use App\Domains\Narrative\Services\NarrativeBridge;
use App\Domains\Narrative\Serial\Contracts\StructuredSummaryGeneratorInterface;
use App\Models\ChapterTelemetry;
use App\Models\NarrativeSeries;
use App\Models\NarrativeState;
use App\Models\SerialChapter;
use App\Models\NarrativeArcOutline;
use App\Models\StoryBible;
use App\Models\World;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service sinh truyện dài kỳ: tạo series, lấy thông tin, sinh chương tiếp theo với continuity.
 *
 * Production-safe: transaction + row lock (lockForUpdate) để tránh race condition khi multi worker;
 * không dùng recursion khi chuyển arc; story memory giới hạn (last N chương) để scale.
 */
class SerialStoryService
{
    private const DEFAULT_CHAPTERS_PER_ARC = 6;

    /** Số chương tối đa đưa vào story_so_far (tránh O(n²) và vượt context LLM). */
    private const STORY_MEMORY_CHAPTER_LIMIT = 5;

    public function __construct(
        private readonly SerialArcPlanner $arcPlanner,
        private readonly ChapterProducer $chapterProducer,
        private readonly ?StructuredSummaryGeneratorInterface $structuredSummaryGenerator = null,
        private readonly ?CosmologyRepository $cosmologyRepository = null,
        private readonly ?NarrativeBridge $narrativeBridge = null,
        private readonly ?BeatPlanner $beatPlanner = null,
        private readonly ?NarrativeMemoryService $narrativeMemory = null,
        private readonly ?StoryEventExtractorInterface $storyEventExtractor = null,
        private readonly ?WorldMutationPolicy $worldMutationPolicy = null,
        private readonly ?LayeredProducer $layeredProducer = null,
        private readonly ?ChapterCriticService $chapterCritic = null,
        private readonly ?ConsistencyValidator $consistencyValidator = null,
        private readonly ?NarrativeToUniverseAdapter $narrativeToUniverseAdapter = null
    ) {
    }

    /**
     * Tạo series mới (truyện dài kỳ).
     * Khi có universe_id mà không truyền genre_key: lấy genesis_preset từ World của Universe (25 loại truyện), nếu không có thì emergent.
     *
     * @param array{title: string, genre_key?: string|null, universe_id?: string|null, config?: array} $input
     */
    public function createSeries(array $input): NarrativeSeries
    {
        $universeId = $input['universe_id'] ?? null;
        $genreKey = $input['genre_key'] ?? null;
        if ($genreKey === null && $universeId !== null) {
            $genreKey = $this->resolveGenreKeyFromUniverse($universeId);
        }
        $genreKey = $genreKey ?? SerialGenrePreset::FANTASY_SCHOOL;
        $preset = SerialGenrePreset::get($genreKey);
        $config = array_merge([
            'genre_key' => $genreKey,
            'books_count' => $preset['books_count'],
        ], $input['config'] ?? []);

        return NarrativeSeries::create([
            'title' => $input['title'],
            'genre_key' => $genreKey,
            'universe_id' => $universeId,
            'config' => $config,
            'current_book_index' => 0,
            'total_chapters_generated' => 0,
        ]);
    }

    /**
     * Inject Story Bible (synopsis, braindump, active characters) into chronicleContext when present.
     * Limits to 10 active characters for token safety.
     */
    private function injectStoryBibleIntoChronicleContext(string $seriesId, array &$chronicleContext): void
    {
        $bible = StoryBible::where('narrative_series_id', $seriesId)->with('activeCharacters')->first();
        if ($bible === null) {
            return;
        }
        if ($bible->synopsis !== null && $bible->synopsis !== '') {
            $chronicleContext['synopsis'] = $bible->synopsis;
        }
        if ($bible->braindump !== null && $bible->braindump !== '') {
            $chronicleContext['braindump'] = $bible->braindump;
        }
        if ($bible->style_notes !== null && $bible->style_notes !== '') {
            $chronicleContext['style_notes'] = $bible->style_notes;
        }
        if ($bible->worldbuilding_rules !== null && $bible->worldbuilding_rules !== []) {
            $chronicleContext['worldbuilding_rules'] = $bible->worldbuilding_rules;
        }
        $characters = $bible->activeCharacters->take(10);
        if ($characters->isNotEmpty()) {
            $chronicleContext['story_bible_characters'] = $characters->map(fn ($c) => [
                'name' => $c->name,
                'role' => $c->role ?? '',
                'traits' => is_array($c->traits) ? implode(', ', $c->traits) : (string) ($c->traits ?? ''),
            ])->values()->all();
        }
    }

    /**
     * Resolve genre_key from Universe's World (genesis_preset). Phase 3: read via CosmologyRepository, no Eloquent Universe in Narrative.
     */
    private function resolveGenreKeyFromUniverse(string $universeId): string
    {
        if ($this->cosmologyRepository === null) {
            return SerialGenrePreset::EMERGENT;
        }
        $worldId = $this->cosmologyRepository->getWorldIdForUniverse($universeId);
        if ($worldId === null) {
            return SerialGenrePreset::EMERGENT;
        }
        $world = World::find($worldId);
        if ($world === null) {
            return SerialGenrePreset::EMERGENT;
        }
        $presetKey = $world->preset ?? null;
        if ($presetKey === null || $presetKey === '') {
            return SerialGenrePreset::EMERGENT;
        }
        $supported = SerialGenrePreset::supportedGenres();
        return in_array($presetKey, $supported, true) ? $presetKey : SerialGenrePreset::EMERGENT;
    }

    /**
     * Lấy series theo id (kèm arcs đã plan).
     * Khi series có universe_id: arcs lấy từ evolution (emergent); không thì từ preset.
     *
     * @return array{series: NarrativeSeries, arcs: list<array{book_index: int, title: string, one_line: string}>}
     */
    public function getSeries(string $seriesId): array
    {
        $series = NarrativeSeries::findOrFail($seriesId);
        $config = $series->config ?? [];
        $config['genre_key'] = $series->genre_key;

        $worldState = null;
        $parameters = null;
        if ($series->universe_id && $this->cosmologyRepository !== null) {
            $universe = $this->cosmologyRepository->find($series->universe_id);
            if ($universe !== null) {
                $worldState = $universe->getState()->getAll();
                $parameters = $universe->getParameters();
            }
        }

        $arcs = $this->resolveArcsForSeries($series, $config, $worldState, $parameters);

        return [
            'series' => $series,
            'arcs' => $arcs,
        ];
    }

    /**
     * Arcs from narrative_arc_outlines (level=arc) when present; else SerialArcPlanner.
     *
     * @return list<array{book_index: int, title: string, one_line: string}>
     */
    private function resolveArcsForSeries(
        NarrativeSeries $series,
        array $config,
        ?array $worldState,
        ?array $parameters
    ): array {
        $outlines = NarrativeArcOutline::where('narrative_series_id', $series->id)
            ->where('level', NarrativeArcOutline::LEVEL_ARC)
            ->orderBy('index')
            ->get();
        if ($outlines->isNotEmpty()) {
            return $outlines->map(fn ($o) => [
                'book_index' => $o->index,
                'title' => $o->title ?? 'Tập ' . ($o->index + 1),
                'one_line' => $o->one_line ?? 'Arc ' . ($o->index + 1),
                'status' => $o->status ?? NarrativeArcOutline::STATUS_DRAFT,
                'outline_id' => $o->id,
            ])->values()->all();
        }
        return $this->arcPlanner->planArcsForSeries($config, $worldState, $parameters);
    }

    /**
     * Sinh chương tiếp theo; cập nhật series (total_chapters_generated, current_book_index).
     * Trả về chapter vừa sinh hoặc null nếu đã hết kế hoạch (tất cả arc đã đủ chương).
     *
     * Thread-safe: dùng transaction + lockForUpdate để tránh duplicate chapter khi multi worker.
     * Không dùng recursion: chuyển arc bằng vòng lặp trong transaction, chỉ sinh tối đa 1 chương mỗi lần gọi.
     */
    public function generateNextChapter(string $seriesId): ?SerialChapter
    {
        return DB::transaction(function () use ($seriesId): ?SerialChapter {
            $series = NarrativeSeries::where('id', $seriesId)->lockForUpdate()->first();
            if ($series === null) {
                return null;
            }

            $config = $series->config ?? [];
            $config['genre_key'] = $series->genre_key;

            $universe = null;
            if ($series->universe_id && $this->cosmologyRepository !== null) {
                $universe = $this->cosmologyRepository->find($series->universe_id);
            }
            $worldState = $universe !== null ? $universe->getState()->getAll() : null;
            $parameters = $universe !== null ? $universe->getParameters() : null;
            $arcs = $this->resolveArcsForSeries($series, $config, $worldState, $parameters);
            $chaptersPerArc = (int) ($config['chapters_per_arc'] ?? self::DEFAULT_CHAPTERS_PER_ARC);

            $bookIndex = $series->current_book_index;

            while ($bookIndex < count($arcs)) {
                $arc = $arcs[$bookIndex];
                if (($config['require_arc_approval'] ?? false) && isset($arc['status']) && $arc['status'] !== NarrativeArcOutline::STATUS_APPROVED) {
                    return null;
                }
                $nextChapterIndex = SerialChapter::where('narrative_series_id', $series->id)
                    ->where('book_index', $bookIndex)
                    ->count();

                if ($nextChapterIndex >= $chaptersPerArc) {
                    $series->update(['current_book_index' => $bookIndex + 1]);
                    $bookIndex++;
                    continue;
                }

                $chronicleContext = array_merge(
                    ['series_title' => $series->title, 'arc' => $arc['one_line']],
                    $config
                );
                $this->injectStoryBibleIntoChronicleContext($series->id, $chronicleContext);
                $lorebook = app(LorebookResolver::class)->resolveForChapter($series->id, $arc['one_line'] ?? '');
                foreach ($lorebook as $key => $value) {
                    $chronicleContext[$key] = $value;
                }
                if ($universe !== null) {
                    $chronicleContext['world_state'] = $universe->getState()->getAll();
                    if ($this->narrativeBridge !== null) {
                        $rich = $this->narrativeBridge->getRichContextForChronicle($universe->getState());
                        $chronicleContext['genre'] = $rich['genre'];
                        $chronicleContext['traits'] = $rich['traits'];
                        $chronicleContext['situations'] = $rich['situations'];
                    }
                }
                $narrativeStateRow = NarrativeState::where('narrative_series_id', $series->id)->first();
                $chronicleContext['current_world_state_narrative'] = StateSerializerForPrompt::serialize(
                    $narrativeStateRow?->narrative_driven_state
                );
                $styleInput = $series->genre_key;
                if ($universe !== null && $this->narrativeBridge !== null) {
                    $styleInput = $this->narrativeBridge->getRichContextForChronicle($universe->getState());
                }

                $result = $this->produceNextChapterContent(
                    $series,
                    $arc,
                    $nextChapterIndex,
                    $chaptersPerArc,
                    $chronicleContext,
                    $styleInput,
                    $worldState,
                    $parameters
                );
                $content = $result['content'];
                $beatSpec = $result['beat_spec'] ?? null;
                $usage = $result['usage'] ?? null;

                $summary = $this->summarizeChapter($content);
                $structuredSummary = $this->structuredSummaryGenerator?->generate($content);

                $chapter = SerialChapter::create([
                    'narrative_series_id' => $series->id,
                    'book_index' => $bookIndex,
                    'chapter_index' => $nextChapterIndex,
                    'content' => $content,
                    'summary' => $summary,
                    'structured_summary' => $structuredSummary,
                ]);

                $series->increment('total_chapters_generated');

                if ($beatSpec !== null) {
                    $this->updateFingerprint(
                        $series->id,
                        $beatSpec,
                        $nextChapterIndex,
                        $chaptersPerArc,
                        $worldState
                    );
                }

                $this->projectNarrativeEventsToWorld($series->id, $content);

                if ($this->consistencyValidator !== null) {
                    $report = $this->consistencyValidator->validate($series->id, $content);
                    if ($report['has_conflicts']) {
                        $chapter->update([
                            'needs_review' => true,
                            'consistency_notes' => $report['notes'],
                        ]);
                    }
                }

                $this->recordTelemetry($chapter, $beatSpec, $usage, $content);

                return $chapter;
            }

            return null;
        });
    }

    /**
     * Produce content for the next chapter. Uses BeatSpec + MemorySnapshot when BeatPlanner and NarrativeMemory are available.
     *
     * @return array{content: string, beat_spec: BeatSpec|null, usage: array|null}
     */
    private function produceNextChapterContent(
        NarrativeSeries $series,
        array $arc,
        int $nextChapterIndex,
        int $chaptersPerArc,
        array $chronicleContext,
        string|array $styleInput,
        ?array $worldState,
        ?array $parameters
    ): array {
        if ($this->beatPlanner !== null && $this->narrativeMemory !== null) {
            $beatSpec = $this->beatPlanner->planForChapter(
                $arc,
                $nextChapterIndex,
                $chaptersPerArc,
                $worldState,
                $parameters
            );
            $lastBeat = NarrativeState::where('narrative_series_id', $series->id)->value('last_emotional_beat') ?? '';
            $memory = $this->narrativeMemory->snapshotForSeries(
                $series,
                $nextChapterIndex,
                $chaptersPerArc,
                $lastBeat
            );

            $useQualityPipeline = ($series->config['quality_pipeline'] ?? false) && $this->layeredProducer !== null && $this->chapterCritic !== null;
            if ($useQualityPipeline) {
                $content = '';
                $usage = null;
                $maxRetries = $this->chapterCritic->getMaxRetries();
                for ($retry = 0; $retry <= $maxRetries; $retry++) {
                    $specResult = $this->layeredProducer->produce($beatSpec, $memory, $chronicleContext, $styleInput);
                    $content = $specResult['content'];
                    $usage = $specResult['usage'] ?? null;
                    $critique = $this->chapterCritic->critique($content, $series->genre_key ?? 'wuxia');
                    if ($critique['pass']) {
                        break;
                    }
                }
                return ['content' => $content, 'beat_spec' => $beatSpec, 'usage' => $usage];
            }

            $specResult = $this->chapterProducer->produceSerialChapterFromSpec(
                $beatSpec,
                $memory,
                $chronicleContext,
                $styleInput
            );
            return [
                'content' => $specResult['content'],
                'beat_spec' => $beatSpec,
                'usage' => $specResult['usage'] ?? null,
            ];
        }

        $blueprints = $this->arcPlanner->planChaptersForArc($arc, $chaptersPerArc);
        $blueprint = $blueprints[$nextChapterIndex];
        $blueprint['chapter_index'] = $series->total_chapters_generated;
        $storySoFar = $this->buildStorySoFar($series);
        $content = $this->chapterProducer->produceSerialChapter(
            $blueprint,
            $chronicleContext,
            $storySoFar,
            $styleInput
        );
        return ['content' => $content, 'beat_spec' => null, 'usage' => null];
    }

    /**
     * Build story_so_far chỉ từ N chương gần nhất (tránh O(n) load + vượt context LLM).
     */
    private function buildStorySoFar(NarrativeSeries $series): string
    {
        $chapters = SerialChapter::where('narrative_series_id', $series->id)
            ->orderBy('book_index', 'desc')
            ->orderBy('chapter_index', 'desc')
            ->limit(self::STORY_MEMORY_CHAPTER_LIMIT)
            ->get()
            ->reverse()
            ->values();

        if ($chapters->isEmpty()) {
            return '';
        }

        $parts = [];
        foreach ($chapters as $ch) {
            $parts[] = sprintf(
                'Tập %d, Chương %d: %s',
                $ch->book_index + 1,
                $ch->chapter_index + 1,
                $ch->summary ?? Str::limit(strip_tags($ch->content ?? ''), 150)
            );
        }
        return implode("\n", $parts);
    }

    private function summarizeChapter(string $content): string
    {
        $cleaned = strip_tags($content);
        return Str::limit($cleaned, 300);
    }

    /**
     * Update structural fingerprint after generating a chapter.
     */
    private function updateFingerprint(
        string $seriesId,
        BeatSpec $beatSpec,
        int $chapterIndexInArc,
        int $chaptersPerArc,
        ?array $worldState
    ): void {
        $arcProgress = $chaptersPerArc > 0
            ? min(1.0, (float) (($chapterIndexInArc + 1) / $chaptersPerArc))
            : 0.0;

        $worldSnapshot = null;
        if ($worldState !== null && isset($worldState['entropy'])) {
            $worldSnapshot = array_intersect_key(
                $worldState,
                array_flip(['entropy', 'order', 'cohesion', 'innovation'])
            );
        }

        NarrativeState::updateOrCreate(
            ['narrative_series_id' => $seriesId],
            [
                'arc_progress' => $arcProgress,
                'last_emotional_beat' => $beatSpec->emotion,
                'last_tension' => $beatSpec->tension,
                'foreshadow_cooldown' => 0,
                'world_snapshot' => $worldSnapshot,
            ]
        );
    }

    /**
     * Record chapter telemetry (beat, tension, word_count, token usage).
     */
    private function recordTelemetry(SerialChapter $chapter, ?BeatSpec $beatSpec, ?array $usage, string $content): void
    {
        $wordCount = str_word_count(strip_tags($content));
        $payload = [
            'serial_chapter_id' => $chapter->id,
            'narrative_series_id' => $chapter->narrative_series_id,
            'word_count' => $wordCount,
            'generated_at' => $chapter->created_at,
        ];
        if ($beatSpec !== null) {
            $payload['emotional_beat'] = $beatSpec->emotion;
            $payload['tension'] = $beatSpec->tension;
        }
        if ($usage !== null) {
            $payload['prompt_tokens'] = $usage['prompt_tokens'] ?? null;
            $payload['completion_tokens'] = $usage['completion_tokens'] ?? null;
            $payload['total_tokens'] = $usage['total_tokens'] ?? null;
        }
        ChapterTelemetry::create($payload);
    }

    /**
     * Causality bridge: extract story events from chapter, apply to narrative_driven_state, save.
     */
    private function projectNarrativeEventsToWorld(string $seriesId, string $chapterContent): void
    {
        if ($this->storyEventExtractor === null || $this->worldMutationPolicy === null) {
            return;
        }
        $events = $this->storyEventExtractor->extract($chapterContent);
        if ($events === []) {
            return;
        }
        $stateRow = NarrativeState::where('narrative_series_id', $seriesId)->first();
        $current = $stateRow !== null && is_array($stateRow->narrative_driven_state)
            ? $stateRow->narrative_driven_state
            : WorldMutationPolicy::defaultState();
        $newState = $this->worldMutationPolicy->applyEvents($events, $current);
        if ($stateRow !== null) {
            $stateRow->update(['narrative_driven_state' => $newState]);
        } else {
            NarrativeState::create([
                'narrative_series_id' => $seriesId,
                'narrative_driven_state' => $newState,
            ]);
        }

        // Phase 4.3: When config narrative_affects_universe is on, commit to Universe via Mutation (single boundary)
        if ($this->narrativeToUniverseAdapter !== null) {
            $series = NarrativeSeries::find($seriesId);
            if ($series !== null) {
                $this->narrativeToUniverseAdapter->commitFromEvents($series, $events);
            }
        }
    }
}
