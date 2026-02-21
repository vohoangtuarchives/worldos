<?php

namespace Tuzy\Domain\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\World;

class MaterialInstance extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'mutation_state' => 'array',
        'historical_traces' => 'array',
        'retired_at' => 'datetime',
        'strength_level' => 'integer',
        'degradation_level' => 'integer',
        'activation_epoch' => 'integer',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
