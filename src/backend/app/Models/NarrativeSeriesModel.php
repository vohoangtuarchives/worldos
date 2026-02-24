<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NarrativeSeriesModel extends Model
{
    protected $table = 'narrative_series';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'genre',
        'title',
        'current_book_index',
        'total_chapters_generated',
        'require_arc_approval',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_book_index' => 'integer',
            'total_chapters_generated' => 'integer',
            'require_arc_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(SerialChapterModel::class, 'series_id');
    }
}
