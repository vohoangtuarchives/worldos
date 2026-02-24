<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StoryBlueprint extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'structure_vector' => 'array',
        'novelty_score' => 'float',
    ];

    public function genesisSeed()
    {
        return $this->belongsTo(GenesisSeed::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function conflict()
    {
        return $this->belongsTo(ConflictPattern::class);
    }

    public function powerSystem()
    {
        return $this->belongsTo(PowerSystem::class);
    }

    public function protagonistArchetype()
    {
        return $this->belongsTo(CharacterArchetype::class);
    }

    public function antagonistArchetype()
    {
        return $this->belongsTo(CharacterArchetype::class);
    }
}
