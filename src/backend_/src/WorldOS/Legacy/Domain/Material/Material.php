<?php

namespace WorldOS\Legacy\Domain\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use WorldOS\Legacy\Domain\Material\Enums\MaterialOntology;
use WorldOS\Legacy\Domain\Material\Enums\MaterialFunction;
use WorldOS\Legacy\Domain\Material\Enums\MaterialLifecycle;

class Material extends Model
{
    use HasUuids;

    // Immutable foundation - no mass assignment protection needed as it's strictly controlled
    protected $guarded = [];

    protected $casts = [
        'ontology' => MaterialOntology::class,
        'function' => MaterialFunction::class,
        'default_lifecycle' => MaterialLifecycle::class,
        'origin_sources' => 'array',
        'preconditions' => 'array',
        'pressure_inputs' => 'array',
        'pressure_outputs' => 'array',
        'incompatible_with' => 'array',
        'mutation_axes' => 'array',
    ];

    public function instances()
    {
        return $this->hasMany(MaterialInstance::class);
    }
}
