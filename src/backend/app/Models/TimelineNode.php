<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineNode extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'world_id',
        'parent_ids',
        'canonical_level', // MAIN, ALTERNATE, DRAFT
        'state_snapshot',
    ];

    protected $casts = [
        'parent_ids' => 'array',
        'state_snapshot' => 'array',
    ];

    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class);
    }
}
