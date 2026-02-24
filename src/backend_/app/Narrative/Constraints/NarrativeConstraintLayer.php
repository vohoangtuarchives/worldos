<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;
use App\Narrative\Exceptions\NarrativeViolationException;

final class NarrativeConstraintLayer
{
    private array $constraints = [];
    private ?ConstraintRegistry $registry = null;

    public function __construct(?ConstraintRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    public function addConstraint(NarrativeConstraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    /**
     * @throws NarrativeViolationException
     */
    public function enforce(NarrativeContext $ctx, string $draft): string
    {
        // 1. Run Registry Rules (DSL)
        if ($this->registry) {
            foreach ($this->registry->applicable($ctx) as $rule) {
                if ($rule->violates($draft)) {
                    throw new NarrativeViolationException($rule->reason);
                }
            }
        }

        // 2. Run Hard-coded PHP Constraints
        foreach ($this->constraints as $constraint) {
            $result = $constraint->check($ctx, $draft);

            if (!$result->pass) {
                throw new NarrativeViolationException($result->reason);
            }
        }

        return $draft;
    }
}
