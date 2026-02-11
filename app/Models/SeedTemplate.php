<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'dimension',
        'severity',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Convert this template to a StoryEngine Seed instance
     */
    public function toSeed(): \App\StoryEngine\Seed
    {
        return new \App\StoryEngine\Seed(
            type: $this->type,
            dimension: $this->dimension,
            severity: $this->severity
        );
    }
}
