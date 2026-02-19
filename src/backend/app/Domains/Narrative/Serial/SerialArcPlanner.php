<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Serial;

use App\Domains\Cosmology\Evolution\ArcPhase;

/**
 * Lên kế hoạch chuỗi arc cho một series (truyện dài kỳ).
 * Mỗi "book" hoặc "volume" tương ứng một arc chính; có thể chia nhỏ thành chapters.
 * Khi có world state + parameters (universe): arc lấy từ evolution arc_phase (emergent).
 * Khi không: dùng SerialGenrePreset (arc_templates cố định).
 */
class SerialArcPlanner
{
    /** Mô tả ngắn theo phase evolution — dùng khi plan emergent từ universe. */
    private const ARC_PHASE_ONE_LINES = [
        ArcPhase::GENESIS->value => 'Khởi nguyên và hình thành',
        ArcPhase::EXPANSION->value => 'Bành trướng và phát triển',
        ArcPhase::GOLDEN_AGE->value => 'Thời hoàng kim và đỉnh cao',
        ArcPhase::STAGNATION->value => 'Trì trệ và bế tắc',
        ArcPhase::CRISIS->value => 'Khủng hoảng và điểm ngoặt',
        ArcPhase::COLLAPSE->value => 'Sụp đổ và tan rã',
        ArcPhase::REFORMATION->value => 'Tái thiết và thay đổi',
    ];

    /** Thứ tự phase gợi ý cho chuỗi arc (cycle khi cần nhiều tập). */
    private const PHASE_ORDER = [
        ArcPhase::GENESIS,
        ArcPhase::EXPANSION,
        ArcPhase::GOLDEN_AGE,
        ArcPhase::STAGNATION,
        ArcPhase::CRISIS,
        ArcPhase::COLLAPSE,
        ArcPhase::REFORMATION,
    ];

    /**
     * Trả về danh sách arc (một arc = một "tập" / book / volume) cho series.
     * Khi $worldState và $parameters được truyền (emergent): dùng arc_phase để tạo arc từ phase.
     *
     * @param array{genre_key?: string, books_count?: int, arc_templates?: list<string>} $config
     * @param array<string, float>|null $worldState World state vector (order, entropy, ...) khi emergent
     * @param array<string, mixed>|null $parameters Universe params (arc_phase, ...) khi emergent
     * @return list<array{book_index: int, title: string, one_line: string}>
     */
    public function planArcsForSeries(array $config, ?array $worldState = null, ?array $parameters = null): array
    {
        $count = (int) ($config['books_count'] ?? 5);
        $genreKey = $config['genre_key'] ?? SerialGenrePreset::FANTASY_SCHOOL;
        $usePhaseBased = ($worldState !== null && $parameters !== null && isset($parameters['arc_phase']))
            || $genreKey === SerialGenrePreset::EMERGENT;

        if ($usePhaseBased) {
            $count = $count ?: (int) (SerialGenrePreset::get(SerialGenrePreset::EMERGENT)['books_count'] ?? 7);
            return $this->planArcsFromPhase($count);
        }

        $preset = SerialGenrePreset::get($genreKey);
        $count = $count ?: $preset['books_count'];
        $templates = $config['arc_templates'] ?? $preset['arc_templates'];

        // Phase 7: Emergent Arc Detection from World State Tension
        $tension = $worldState['tension'] ?? 0.0;
        if ($tension > 0.75) {
            return [
                [
                    'book_index' => 0,
                    'title' => 'The Great Upheaval',
                    'one_line' => "High world tension ({$tension}) has triggered an emergent arc of conflict and transformation.",
                ]
            ];
        }

        if ($templates === []) {
            $templates = ['Arc'];
        }

        $arcs = [];
        for ($i = 0; $i < $count; $i++) {
            $oneLine = $templates[$i % count($templates)] ?? 'Arc ' . ($i + 1);
            $arcs[] = [
                'book_index' => $i,
                'title' => 'Tập ' . ($i + 1),
                'one_line' => $oneLine,
            ];
        }
        return $arcs;
    }

    /**
     * Plan arcs theo thứ tự evolution phase (emergent).
     *
     * @return list<array{book_index: int, title: string, one_line: string}>
     */
    private function planArcsFromPhase(int $booksCount): array
    {
        $arcs = [];
        for ($i = 0; $i < $booksCount; $i++) {
            $phase = self::PHASE_ORDER[$i % count(self::PHASE_ORDER)];
            $oneLine = self::ARC_PHASE_ONE_LINES[$phase->value] ?? $phase->value;
            $arcs[] = [
                'book_index' => $i,
                'title' => 'Tập ' . ($i + 1),
                'one_line' => $oneLine,
            ];
        }
        return $arcs;
    }

    /**
     * Trả về blueprint chapters cho một arc (book) cụ thể.
     * Số chương mỗi arc có thể cấu hình (mặc định 5–7).
     *
     * @param array{book_index: int, title: string, one_line: string} $arc
     * @param int $chaptersPerArc
     * @return list<array{chapter_index: int, emotional_objective: string, conflict_delta: array, motif_targets: array, arc_context: string}>
     */
    public function planChaptersForArc(array $arc, int $chaptersPerArc = 6): array
    {
        $beats = ['opening', 'rising', 'complication', 'midpoint', 'crisis', 'resolution'];
        $blueprints = [];
        for ($i = 0; $i < $chaptersPerArc; $i++) {
            $beat = $beats[$i % count($beats)];
            $blueprints[] = [
                'chapter_index' => $i,
                'emotional_objective' => $beat,
                'conflict_delta' => ['direction' => $i < $chaptersPerArc / 2 ? 'increase' : 'decrease'],
                'motif_targets' => [],
                'arc_context' => $arc['one_line'],
            ];
        }
        return $blueprints;
    }
}
