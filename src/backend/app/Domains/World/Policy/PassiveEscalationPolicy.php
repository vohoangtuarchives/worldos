<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\EscalationPolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\PassiveEscalationPolicy as LegacyPassiveEscalationPolicy;

class PassiveEscalationPolicy extends LegacyPassiveEscalationPolicy implements EscalationPolicy
{
}
