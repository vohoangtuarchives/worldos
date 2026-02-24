<?php

namespace App\Models\Evolution;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosmologicalFieldModel extends Model
{
    use HasUuids;

    protected $table = 'cosmological_fields';

    protected $fillable = [
        'generation_id',
        'entropy_background',
        'turbulence_pressure',
        'mythic_resonance_json',
        'spectral_drift',
    ];

    protected $casts = [
        'mythic_resonance_json' => 'array',
        'entropy_background' => 'float',
        'turbulence_pressure' => 'float',
        'spectral_drift' => 'float',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class, 'generation_id');
    }
}
