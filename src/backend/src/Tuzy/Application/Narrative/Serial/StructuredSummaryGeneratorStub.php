<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Serial;

use Tuzy\Application\Narrative\Serial\Contracts\StructuredSummaryGeneratorInterface;
use Illuminate\Support\Str;

/**
 * Stub: returns minimal structure (one main_event from excerpt) or null.
 * Replace with LLM-based implementation when needed.
 */
class StructuredSummaryGeneratorStub implements StructuredSummaryGeneratorInterface
{
    private const GENERIC_PLACEHOLDER = 'Một sự kiện chấn động đang diễn ra trong dòng chảy thực tại.';

    public function generate(string $content): ?array
    {
        $cleaned = strip_tags($content);
        if ($cleaned === '') {
            return null;
        }
        $trimmed = trim($cleaned);
        // Tránh trùng với summary: không trả về structured khi nội dung là placeholder chung
        if ($trimmed === self::GENERIC_PLACEHOLDER || str_starts_with($trimmed, '[Placeholder —')) {
            return null;
        }
        $excerpt = Str::limit($trimmed, 300);
        if ($excerpt === $trimmed || mb_strlen($trimmed) < 80) {
            return null; // quá ngắn hoặc không cắt — để summary đủ, không cần lặp
        }
        return [
            'main_events' => [$excerpt],
            'character_changes' => [],
            'world_state_delta' => [],
        ];
    }
}
