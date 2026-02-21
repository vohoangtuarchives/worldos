<?php

namespace Tuzy\Application\Narrative\Dialogue\Services;

use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Domain\Narrative\ValueObject\Intent;
use Tuzy\Application\Narrative\Scene\Scene;
use Tuzy\Application\Narrative\Dialogue\Contracts\GuardRule;
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
