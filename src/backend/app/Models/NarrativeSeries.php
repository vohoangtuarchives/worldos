<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Truyện dài kỳ (serial): nhiều tập/volume, genre preset (fantasy_school, wuxia, ...).
 */
class NarrativeSeries extends Model
{
    use HasUuids;

    protected $table = 'narrative_series';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'genre_key',
        'universe_id',
        'config',
        'current_book_index',
        'total_chapters_generated',
    ];

    protected $casts = [
        'config' => 'array',
        'current_book_index' => 'integer',
        'total_chapters_generated' => 'integer',
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(SerialChapter::class, 'narrative_series_id');
    }

    public function storyBible(): HasOne
    {
        return $this->hasOne(StoryBible::class, 'narrative_series_id');
    }

    public function arcOutlines(): HasMany
    {
        return $this->hasMany(NarrativeArcOutline::class, 'narrative_series_id');
    }
}
