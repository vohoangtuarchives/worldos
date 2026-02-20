<?php

namespace App\Domains\CoreTruth\ValueObjects;

use InvalidArgumentException;

/**
 * CoreTruth contains the undeniable, unchanging foundations of a specific Universe or Saga.
 * It is meant to be instantiated once at Genesis and never mutated.
 */
class CoreTruth
{
    /** @var array<string, Axiom> */
    public readonly array $axioms;

    public function __construct(
        public readonly string $genesisHash,
        array $axioms = []
    ) {
        $axiomMap = [];
        foreach ($axioms as $axiom) {
            if (!$axiom instanceof Axiom) {
                throw new InvalidArgumentException("All items must be instances of Axiom");
            }
            $axiomMap[$axiom->id] = $axiom;
        }
        $this->axioms = $axiomMap;
    }

    public function getAxiom(string $id): ?Axiom
    {
        return $this->axioms[$id] ?? null;
    }

    public function hasAxiom(string $id): bool
    {
        return isset($this->axioms[$id]);
    }

    public function toArray(): array
    {
        return [
            'genesis_hash' => $this->genesisHash,
            'axioms' => array_map(fn(Axiom $a) => $a->toArray(), array_values($this->axioms))
        ];
    }

    public static function fromArray(array $data): self
    {
        $axioms = array_map(fn($a) => Axiom::fromArray($a), $data['axioms'] ?? []);
        return new self(
            $data['genesis_hash'],
            $axioms
        );
    }
}
