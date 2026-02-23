<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\PowerLawPolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\LinearPowerPolicy as LegacyLinearPowerPolicy;

class LinearPowerPolicy extends LegacyLinearPowerPolicy implements PowerLawPolicy
{
}
