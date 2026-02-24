<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialChapterModel extends Model
{
    protected $table = 'serial_chapters';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'series_id',
        'book_index',
        'chapter_index',
        'raw_text',
        'status',
        'canonized_at',
    ];

    protected function casts(): array
    {
        return [
            'book_index' => 'integer',
            'chapter_index' => 'integer',
            'canonized_at' => 'datetime',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeriesModel::class, 'series_id');
    }
}
