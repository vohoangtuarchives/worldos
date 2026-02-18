<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Services;

/**
 * Layer 3: Renders structured context (genre vector, situations, pressure, trend) into prose.
 * Template-based; can be extended to call LLM with same payload.
 */
class ChronicleRenderer
{
    public function __construct(
        private readonly SemanticMapper $semanticMapper
    ) {
    }

    /**
     * @param array<string, float> $genreVector e.g. ['utopian' => 0.2, 'dystopian' => 0.7, ...]
     * @param list<array{key: string, intensity: float, velocity: string, persistence: float}> $situations
     * @param array<string, string> $trend e.g. ['entropy' => 'rising', 'inequality' => 'stable']
     * @param array{epoch_label?: string, turning_point?: string}|null $meta
     */
    public function render(
        array $genreVector,
        array $situations,
        float $pressureScore,
        array $trend = [],
        ?array $meta = null,
        int $age = 0
    ): string {
        $parts = [];

        if ($age > 0) {
            $parts[] = "Ở chu kỳ thứ {$age},";
        }

        $primary = $this->primaryGenre($genreVector);
        $intro = $this->introForGenre($primary);
        $parts[] = $intro;

        $maxSituations = 3;
        $count = 0;
        foreach ($situations as $s) {
            if ($count >= $maxSituations) {
                break;
            }
            if ($s['key'] === 'neutral' && count($situations) > 1) {
                continue;
            }
            $meaning = $this->semanticMapper->toNarrativeMeaning($s['key'], $s['velocity']);
            $parts[] = $this->sentenceForMeaning($meaning, $s['intensity']);
            $count++;
        }

        if (!empty($trend)) {
            $trendStr = implode(', ', array_map(fn ($k, $v) => "{$k} {$v}", array_keys($trend), $trend));
            $parts[] = "Xu hướng: {$trendStr}.";
        }

        $parts[] = sprintf('Áp lực: %.2f.', $pressureScore);

        if ($meta !== null && isset($meta['epoch_label'])) {
            $parts[] = "Giai đoạn: {$meta['epoch_label']}.";
        }
        if ($meta !== null && isset($meta['turning_point'])) {
            $parts[] = $meta['turning_point'];
        }

        return implode(' ', $parts);
    }

    private function primaryGenre(array $genreVector): string
    {
        if (empty($genreVector)) {
            return 'neutral';
        }
        arsort($genreVector, SORT_NUMERIC);
        return array_key_first($genreVector);
    }

    private function introForGenre(string $genre): string
    {
        $intros = [
            'utopian' => 'một thực tại gần utopia — hòa bình, ổn định và tương đối công bằng.',
            'dystopian' => 'một thực tại dystopia — đàn áp, bất bình và tầng lớp hóa sâu.',
            'cyberpunk' => 'sắc thái cyberpunk — công nghệ cao, đời sống thấp, tập đoàn thống trị.',
            'eldritch' => 'một thực tại eldritch — điên rồ, phi Euclid và ám ảnh.',
            'war' => 'thế giới chiến tranh — xung đột và tàn phá định nghĩa kỷ nguyên.',
            'decay' => 'sắc thái suy tàn — đổ nát và hoài niệm về những gì đã mất.',
        ];
        return $intros[$genre] ?? 'một thực tại cân bằng, nhiều lực đẩy và kéo chưa phân thắng bại.';
    }

    private function sentenceForMeaning(string $meaning, float $intensity): string
    {
        if ($intensity > 0.8) {
            return "{$meaning} (cực điểm).";
        }
        return "{$meaning}.";
    }
}
