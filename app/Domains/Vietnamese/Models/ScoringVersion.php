<?php

namespace App\Domains\Vietnamese\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ScoringVersion extends Model
{
    use HasUuids;

    protected $table = 'scoring_versions';

    protected $fillable = [
        'version', 'dimension_weights', 'event_dimension_map', 'config',
        'is_active', 'changelog'
    ];

    protected $casts = [
        'dimension_weights' => 'array',
        'event_dimension_map' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    // Get active version
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    // Activate this version (deactivate others)
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    // Get normalization constant
    public function getNormalizationConstantAttribute(): float
    {
        return $this->config['normalization_constant'] ?? 5.0;
    }

    // Get time decay lambda
    public function getTimeDecayLambdaAttribute(): float
    {
        return $this->config['time_decay_lambda'] ?? 0.0003;
    }
}
