<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;

interface NarrativeConstraint
{
    public function check(NarrativeContext $ctx, string $text): ConstraintResult;
}
