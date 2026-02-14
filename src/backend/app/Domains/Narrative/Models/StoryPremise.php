<?php

namespace App\Domains\Narrative\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StoryPremise extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'narrative_story_premises';

    protected $fillable = [
        'title',
        'summary',
        'components', // Array of UUIDs from MaterialSeed
        'power_escalation', // JSON defining tiers
        'is_favorite', // Boolean
    ];

    protected $casts = [
        'components' => 'array',
        'power_escalation' => 'array',
        'is_favorite' => 'boolean',
    ];
}
