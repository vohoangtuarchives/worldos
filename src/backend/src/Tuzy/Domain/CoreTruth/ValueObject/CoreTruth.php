<?php

declare(strict_types=1);

namespace Tuzy\Domain\CoreTruth\ValueObject;

use InvalidArgumentException;

/**
 * Undeniable, unchanging foundations of a Universe or Saga.
 * Instantiated at Genesis and never mutated.
 */
readonly class CoreTruth
{
    /** @var array<string, Axiom> */
    public array $axioms;

    public function __construct(
        public string $genesisHash,
        array $axioms = [],
    ) {
        $axiomMap = [];
        foreach ($axioms as $axiom) {
            if (!$axiom instanceof Axiom) {
                throw new InvalidArgumentException('All items must be instances of Axiom');
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
            'axioms' => array_map(fn (Axiom $a) => $a->toArray(), array_values($this->axioms)),
        ];
    }

    public static function fromArray(array $data): self
    {
        $axioms = array_map(fn ($a) => Axiom::fromArray($a), $data['axioms'] ?? []);
        return new self(
            $data['genesis_hash'],
            $axioms,
        );
    }
}
