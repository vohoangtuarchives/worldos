<?php

namespace App\Models\Evolution;

use App\Models\Universe;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Generation extends Model
{
    use HasUuids;

    protected $table = 'evolution_generations';

    protected $fillable = [
        'experiment_id',
        'generation_index',
        'population_size',
        'status',
    ];

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class, 'experiment_id');
    }

    public function universes(): HasMany
    {
        return $this->hasMany(Universe::class, 'generation_id');
    }

    public function cosmologicalField(): HasOne
    {
        return $this->hasOne(CosmologicalFieldModel::class, 'generation_id');
    }
}
