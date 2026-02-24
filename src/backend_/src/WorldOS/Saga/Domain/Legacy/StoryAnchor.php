<?php

namespace WorldOS\Saga\Domain\Legacy;

use WorldOS\Legacy\Domain\Power\ValueObject\PowerStage;
use WorldOS\Saga\Domain\Legacy\Enums\PowerScope;

class StoryAnchor
{
    public function __construct(
        public readonly PowerStage $anchorStage,
        public readonly int $anchorEpoch,
        public readonly PowerScope $scope = PowerScope::LOCAL
    ) {}
}
