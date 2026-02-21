<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga;

use App\Models\World;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Phase 4.4: One record per world outcome in a saga (for Pareto / generation history).
 */
class SagaGeneration extends Model
{
    use HasUuids;

    protected $fillable = [
        'saga_id',
        'world_id',
        'sequence',
        'objective_vector',
        'archetype',
        'stability_flag',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'objective_vector' => 'array',
        'stability_flag' => 'boolean',
    ];

    public function saga()
    {
        return $this->belongsTo(Saga::class);
    }

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
