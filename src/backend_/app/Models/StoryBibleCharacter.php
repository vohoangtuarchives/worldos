<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nhân vật trong Story Bible: name, role, traits, first_seen_chapter, is_active.
 */
class StoryBibleCharacter extends Model
{
    use HasUuids;

    protected $table = 'story_bible_characters';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'story_bible_id',
        'name',
        'role',
        'traits',
        'first_seen_chapter',
        'is_active',
    ];

    protected $casts = [
        'traits' => 'array',
        'is_active' => 'boolean',
        'first_seen_chapter' => 'integer',
    ];

    public function storyBible(): BelongsTo
    {
        return $this->belongsTo(StoryBible::class, 'story_bible_id');
    }
}
