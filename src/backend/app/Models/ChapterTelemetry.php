<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Telemetry per generated chapter: beat, tension, word_count, token usage.
 */
class ChapterTelemetry extends Model
{
    protected $table = 'chapter_telemetry';

    protected $fillable = [
        'serial_chapter_id',
        'narrative_series_id',
        'emotional_beat',
        'tension',
        'word_count',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'generated_at',
    ];

    protected $casts = [
        'tension' => 'float',
        'word_count' => 'integer',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'generated_at' => 'datetime',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(SerialChapter::class, 'serial_chapter_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeries::class, 'narrative_series_id');
    }
}
