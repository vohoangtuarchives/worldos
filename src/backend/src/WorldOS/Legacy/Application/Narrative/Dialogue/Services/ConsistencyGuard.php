<?php

namespace WorldOS\Legacy\Application\Narrative\Dialogue\Services;

use WorldOS\Legacy\Application\Narrative\Character\Character;
use WorldOS\Saga\Domain\Narrative\ValueObject\Intent;
use WorldOS\Legacy\Application\Narrative\Scene\Scene;
use WorldOS\Legacy\Application\Narrative\Dialogue\Contracts\GuardRule;
use Exception;

class ConsistencyGuard
{
    protected array $rules = [];

    public function addRule(GuardRule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @throws Exception If validation fails
     */
    public function validate(Character $actor, Intent $intent, Scene $scene): void
    {
        foreach ($this->rules as $rule) {
            if (! $rule->allows($actor, $intent, $scene)) {
                throw new Exception("Guard Violation: " . $rule->failureReason());
            }
        }
    }
}
