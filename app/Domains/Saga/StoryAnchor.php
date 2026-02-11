<?php

namespace App\Domains\Saga;

use App\Domains\Power\Enums\PowerStage;
use App\Domains\Saga\Enums\PowerScope;

class StoryAnchor
{
    public function __construct(
        public readonly PowerStage $anchorStage,
        public readonly int $anchorEpoch,
        public readonly PowerScope $scope = PowerScope::LOCAL
    ) {}
}
