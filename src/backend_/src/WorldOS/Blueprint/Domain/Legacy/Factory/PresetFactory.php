<?php

namespace WorldOS\Blueprint\Domain\Legacy\Factory;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ConflictPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\EscalationPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\PowerLawPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ResourcePolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\WorldPreset;
use WorldOS\Legacy\Application\World\Preset\DynamicWorldPreset;
use WorldOS\Legacy\Application\World\Services\PolicyRegistry;
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
