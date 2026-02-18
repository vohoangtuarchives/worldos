<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một chương đã sinh của truyện dài kỳ (serial).
 */
class SerialChapter extends Model
{
    protected $table = 'serial_chapters';

    protected $fillable = [
        'narrative_series_id',
        'book_index',
        'chapter_index',
        'content',
        'summary',
        'structured_summary',
        'needs_review',
        'consistency_notes',
    ];

    protected $casts = [
        'book_index' => 'integer',
        'chapter_index' => 'integer',
        'structured_summary' => 'array',
        'needs_review' => 'boolean',
        'consistency_notes' => 'array',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeries::class, 'narrative_series_id');
    }
}
