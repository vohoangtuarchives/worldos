<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Story Bible: 1-1 với NarrativeSeries. Braindump, synopsis, style_notes; characters.
 */
class StoryBible extends Model
{
    use HasUuids;

    protected $table = 'story_bibles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'narrative_series_id',
        'braindump',
        'synopsis',
        'style_notes',
        'worldbuilding_rules',
    ];

    protected $casts = [
        'worldbuilding_rules' => 'array',
    ];

    public function narrativeSeries(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeries::class, 'narrative_series_id');
    }

    public function characters(): HasMany
    {
        return $this->hasMany(StoryBibleCharacter::class, 'story_bible_id');
    }

    /** Active characters only (for prompt injection). */
    public function activeCharacters(): HasMany
    {
        return $this->hasMany(StoryBibleCharacter::class, 'story_bible_id')->where('is_active', true);
    }
}
