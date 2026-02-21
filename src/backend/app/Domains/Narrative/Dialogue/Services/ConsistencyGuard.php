<?php

namespace App\Domains\Narrative\Dialogue\Services;

use App\Domains\Narrative\Character\Character;
use Tuzy\Domain\Narrative\ValueObject\Intent;
use App\Domains\Narrative\Scene\Scene;
use App\Domains\Narrative\Dialogue\Contracts\GuardRule;
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
