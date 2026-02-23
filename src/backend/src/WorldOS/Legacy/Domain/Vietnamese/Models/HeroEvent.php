<?php

namespace WorldOS\Legacy\Domain\Vietnamese\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroEvent extends Model
{
    use HasUuids;

    protected $table = 'hero_events';

    protected $fillable = [
        'hero_id', 'event_type', 'name', 'description', 'year',
        'scale', 'duration_years', 'success', 'metadata'
    ];

    protected $casts = [
        'year' => 'integer',
        'scale' => 'integer',
        'duration_years' => 'integer',
        'success' => 'float',
        'metadata' => 'array',
    ];

    // Relationship
    public function hero(): BelongsTo
    {
        return $this->belongsTo(VietnameseHero::class, 'hero_id');
    }

    // Auto-recalculate hero scores when events change
    protected static function booted(): void
    {
        static::saved(function (HeroEvent $event) {
            $event->hero->recalculateScores();
        });

        static::deleted(function (HeroEvent $event) {
            $event->hero->recalculateScores();
        });
    }

    // Helper: Get event contribution description
    public function getContributionDescriptionAttribute(): string
    {
        return sprintf(
            '%s (scale: %d, %d years, success: %.0f%%)',
            $this->event_type,
            $this->scale,
            $this->duration_years,
            $this->success * 100
        );
    }
}
