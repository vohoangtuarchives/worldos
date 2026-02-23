<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\ResourcePolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\InfiniteResourcePolicy as LegacyInfiniteResourcePolicy;

class InfiniteResourcePolicy extends LegacyInfiniteResourcePolicy implements ResourcePolicy
{
}
