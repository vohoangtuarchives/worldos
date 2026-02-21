<?php

namespace Tuzy\Domain\Evolution\ValueObject;

use InvalidArgumentException;

class CoreTruth
{
    /** @var array<string, Axiom> */
    public readonly array $axioms;

    public function __construct(array $axioms = [])
    {
        $axiomMap = [];
        foreach ($axioms as $axiom) {
            if (!$axiom instanceof Axiom) {
                throw new InvalidArgumentException("All items must be instances of Axiom");
            }
            $axiomMap[$axiom->id] = $axiom;
        }
        $this->axioms = $axiomMap;
    }

    public function getAxioms(): array
    {
        return array_values($this->axioms);
    }
}


