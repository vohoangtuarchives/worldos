<?php

namespace App\Domains\World\Factory;

use App\Domains\World\Contracts\Policy\ConflictPolicy;
use App\Domains\World\Contracts\Policy\EscalationPolicy;
use App\Domains\World\Contracts\Policy\PowerLawPolicy;
use App\Domains\World\Contracts\Policy\ResourcePolicy;
use App\Domains\World\Contracts\WorldPreset;
use App\Domains\World\Preset\DynamicWorldPreset;
use App\Domains\World\Services\PolicyRegistry;
use App\Models\World\WorldPreset as WorldPresetModel;

class PresetFactory
{
    public function __construct(
        protected PolicyRegistry $registry
    ) {}

    public function build(WorldPresetModel $model): WorldPreset
    {
        return new DynamicWorldPreset(
            id: $model->id,
            code: $model->code,
            name: $model->name,
            powerPolicy: $this->registry->resolve($model->power_policy, PowerLawPolicy::class),
            resourcePolicy: $this->registry->resolve($model->resource_policy, ResourcePolicy::class),
            conflictPolicy: $this->registry->resolve($model->conflict_policy, ConflictPolicy::class),
            escalationPolicy: $this->registry->resolve($model->escalation_policy, EscalationPolicy::class),
            mythPolicy: $model->myth_policy ? $this->registry->resolve($model->myth_policy) : null,
            scarPolicy: $model->scar_policy ? $this->registry->resolve($model->scar_policy) : null,
        );
    }
}
