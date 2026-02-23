<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\ConflictPolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\NoConflictPolicy as LegacyNoConflictPolicy;

class NoConflictPolicy extends LegacyNoConflictPolicy implements ConflictPolicy
{
}
