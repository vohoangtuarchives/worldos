<?php

namespace App\Models\Evolution;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experiment extends Model
{
    use HasUuids;

    protected $table = 'evolution_experiments';

    protected $fillable = [
        'name',
        'status',
    ];

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class, 'experiment_id')->orderBy('generation_index');
    }
}
