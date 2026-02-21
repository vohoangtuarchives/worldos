<?php

namespace App\Domains\Saga;

use Tuzy\Domain\Power\ValueObject\PowerStage;
use Tuzy\Domain\Saga\Enums\PowerScope;

class StoryAnchor
{
    public function __construct(
        public readonly PowerStage $anchorStage,
        public readonly int $anchorEpoch,
        public readonly PowerScope $scope = PowerScope::LOCAL
    ) {}
}
